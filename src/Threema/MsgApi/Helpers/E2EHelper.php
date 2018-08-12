<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Helpers;

use Threema\Core\Exception;
use Threema\MsgApi\Commands\Results\CapabilityResult;
use Threema\MsgApi\Connection;
use Threema\MsgApi\Messages\FileMessage;
use Threema\MsgApi\Messages\ImageMessage;
use Threema\MsgApi\Messages\ThreemaMessage;
use Threema\MsgApi\Tools\CryptTool;

class E2EHelper
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CryptTool
     */
    private $cryptTool;

    /**
     * @var string (bin)
     */
    private $privateKey;

    /**
     * @param string     $privateKey (binary)
     * @param Connection $connection
     * @param CryptTool  $cryptTool
     */
    public function __construct(string $privateKey, Connection $connection, CryptTool $cryptTool = null)
    {
        $this->connection = $connection;
        $this->cryptTool  = $cryptTool;
        $this->privateKey = $privateKey;

        if (null === $this->cryptTool) {
            $this->cryptTool = CryptTool::getInstance();
        }
    }

    /**
     * Encrypt a text message and send it to the threemaId
     *
     * @param string $threemaId
     * @param string $receiverPublicKey binary format
     * @param string $text
     * @return \Threema\MsgApi\Commands\Results\SendE2EResult
     * @throws \Threema\Core\Exception
     */
    public final function sendTextMessage(string $threemaId, string $receiverPublicKey, string $text)
    {
        //random nonce first
        $nonce = $this->cryptTool->randomNonce();

        //create a box
        $textMessage = $this->cryptTool->encryptMessageText(
            $text,
            $this->privateKey,
            $receiverPublicKey,
            $nonce);

        return $this->connection->sendE2E($threemaId, $nonce, $textMessage);
    }

    /**
     * Encrypt an image file, upload the blob and send the image message to the threemaId
     *
     * @param string $threemaId
     * @param string $receiverPublicKey binary format
     * @param string $imagePath
     * @return \Threema\MsgApi\Commands\Results\SendE2EResult
     * @throws \Threema\Core\Exception
     */
    public final function sendImageMessage(string $threemaId, string $receiverPublicKey, string $imagePath)
    {
        //analyse the file
        $fileAnalyzeResult = FileAnalysisTool::analyse($imagePath);

        if (null === $fileAnalyzeResult) {
            throw new Exception('could not analyze the file');
        }

        if (false === in_array($fileAnalyzeResult->getMimeType(), [
                'image/jpg',
                'image/jpeg',
                'image/png'])) {
            throw new Exception('file is not a jpg or png');
        }

        $this->assertIsCapable($threemaId, CapabilityResult::IMAGE);

        //encrypt the image file
        $encryptionResult = $this->cryptTool->encryptImage(file_get_contents($imagePath), $this->privateKey,
            $receiverPublicKey);
        $uploadResult     = $this->connection->uploadFile($encryptionResult->getData());
        if (!$uploadResult->isSuccess()) {
            throw new Exception('could not upload the image (' . $uploadResult->getErrorCode() . ' ' . $uploadResult->getErrorMessage() . ') ' . $uploadResult->getRawResponse());
        }

        $nonce = $this->cryptTool->randomNonce();

        //create a image message box
        $imageMessage = $this->cryptTool->encryptImageMessage(
            $uploadResult,
            $encryptionResult,
            $this->privateKey,
            $receiverPublicKey,
            $nonce);

        return $this->connection->sendE2E($threemaId, $nonce, $imageMessage);
    }

    /**
     * Encrypt a file (and thumbnail if given), upload the blob and send it to the given threemaId
     *
     * @param string      $threemaId
     * @param string      $receiverPublicKey binary format
     * @param string      $filePath
     * @param null|string $thumbnailPath
     * @return \Threema\MsgApi\Commands\Results\SendE2EResult
     * @throws \Threema\Core\Exception
     */
    public final function sendFileMessage(string $threemaId, string $receiverPublicKey, string $filePath,
        ?string $thumbnailPath = null)
    {
        //analyse the file
        $fileAnalyzeResult = FileAnalysisTool::analyse($filePath);

        if (null === $fileAnalyzeResult) {
            throw new Exception('could not analyze the file');
        }

        $this->assertIsCapable($threemaId, CapabilityResult::FILE);

        //encrypt the main file
        $encryptionResult = $this->cryptTool->encryptFile(file_get_contents($filePath));
        $uploadResult     = $this->connection->uploadFile($encryptionResult->getData());

        if (!$uploadResult->isSuccess()) {
            throw new Exception('could not upload the file (' . $uploadResult->getErrorCode() . ' ' . $uploadResult->getErrorMessage() . ') ' . $uploadResult->getRawResponse());
        }

        $thumbnailUploadResult = null;

        //encrypt the thumbnail file (if exists)
        if (strlen($thumbnailPath) > 0 && true === file_exists($thumbnailPath)) {
            //encrypt the main file
            $thumbnailEncryptionResult = $this->cryptTool->encryptFileThumbnail(file_get_contents($thumbnailPath),
                $encryptionResult->getKey());
            $thumbnailUploadResult     = $this->connection->uploadFile($thumbnailEncryptionResult->getData());

            if (!$thumbnailUploadResult->isSuccess()) {
                throw new Exception('could not upload the thumbnail file (' . $thumbnailUploadResult->getErrorCode() . ' ' . $thumbnailUploadResult->getErrorMessage() . ') ' . $thumbnailUploadResult->getRawResponse());
            }
        }

        $nonce = $this->cryptTool->randomNonce();

        //create a file message box
        $fileMessage = $this->cryptTool->encryptFileMessage(
            $uploadResult,
            $encryptionResult,
            $thumbnailUploadResult,
            $fileAnalyzeResult,
            $this->privateKey,
            $receiverPublicKey,
            $nonce);

        return $this->connection->sendE2E($threemaId, $nonce, $fileMessage);
    }

    /**
     * Decrypt a message and download the files of the message to the $outputFolder
     *
     * Note: This does not check the MAC before, which you should always do when
     * you want to use this in your own application! Use {@link checkMac()} for doing so.
     *
     * @param string            $senderPublicKey   binary format
     * @param string            $messageId
     * @param string            $box               box as binary string
     * @param string            $nonce             nonce as binary string
     * @param string|null|false $outputFolder      folder for storing the files,
     *                                             null=current folder, false=do not download files
     * @param \Closure          $shouldDownload
     * @return ReceiveMessageResult
     * @throws \Threema\Core\Exception
     * @throws \Threema\MsgApi\Exceptions\BadMessageException
     * @throws \Threema\MsgApi\Exceptions\DecryptionFailedException
     * @throws \Threema\MsgApi\Exceptions\UnsupportedMessageTypeException
     */
    public final function receiveMessage(
        string $senderPublicKey,
        $messageId,
        $box,
        $nonce,
        $outputFolder = null,
        \Closure $shouldDownload = null)
    {
        $message = $this->cryptTool->decryptMessage(
            $box,
            $this->privateKey,
            $senderPublicKey,
            $nonce
        );

        if (null === $message || false === is_object($message)) {
            throw new Exception('Could not encrypt box');
        }

        $receiveResult = new ReceiveMessageResult($messageId, $message);

        if ($outputFolder === false) {
            return $receiveResult;
        }
        if ($outputFolder === null || strlen($outputFolder) == 0) {
            $outputFolder = '.';
        }
        if ($shouldDownload === null) {
            $shouldDownload = function () {
                return true;
            };
        }

        if ($message instanceof ImageMessage) {
            $result = $this->downloadFile($message, $message->getBlobId(), $shouldDownload);
            if (null !== $result && true === $result->isSuccess()) {
                $image = $this->cryptTool->decryptImage(
                    $result->getData(),
                    $senderPublicKey,
                    $this->privateKey,
                    $message->getNonce()
                );

                if (null === $image) {
                    throw new Exception('decryption of image failed');
                }
                //save file
                $filePath = $outputFolder . '/' . $messageId . '.jpg';
                $f        = fopen($filePath, 'w+');
                fwrite($f, $image);
                fclose($f);

                $receiveResult->addFile('image', $filePath);
            }
        } else if ($message instanceof FileMessage) {
            $result = $this->downloadFile($message, $message->getBlobId(), $shouldDownload);

            if (null !== $result && true === $result->isSuccess()) {
                $file = $this->cryptTool->decryptFile(
                    $result->getData(),
                    $this->cryptTool->hex2bin($message->getEncryptionKey()));

                if (null === $file) {
                    throw new Exception('file decryption failed');
                }

                //save file
                $filePath = $outputFolder . '/' . $messageId . '-' . $message->getFilename();
                file_put_contents($filePath, $file);

                $receiveResult->addFile('file', $filePath);
            }

            if (null !== $message->getThumbnailBlobId() && strlen($message->getThumbnailBlobId()) > 0) {
                $result = $this->downloadFile($message, $message->getThumbnailBlobId(), $shouldDownload);
                if (null !== $result && true === $result->isSuccess()) {
                    $file = $this->cryptTool->decryptFileThumbnail(
                        $result->getData(),
                        $this->cryptTool->hex2bin($message->getEncryptionKey()));

                    if (null === $file) {
                        throw new Exception('thumbnail decryption failed');
                    }
                    //save file
                    $filePath = $outputFolder . '/' . $messageId . '-thumbnail-' . $message->getFilename();
                    file_put_contents($filePath, $file);

                    $receiveResult->addFile('thumbnail', $filePath);
                }
            }
        }

        return $receiveResult;
    }

    /**
     * Check the HMAC of an ingoing Threema request. Always do this before de-
     * crypting the message.
     *
     * @param string $threemaId
     * @param string $gatewayId
     * @param string $messageId
     * @param string $date
     * @param string $nonce nonce as hex encoded string
     * @param string $box   box as hex encoded string
     * @param string $mac   the original one send by the server
     * @param string $secret
     * @return bool true if check was successfull, false if not
     */
    public final function checkMac($threemaId, $gatewayId, $messageId, $date, $nonce, $box, $mac, $secret)
    {
        $calculatedMac = hash_hmac('sha256', $threemaId . $gatewayId . $messageId . $date . $nonce . $box, $secret);
        return $this->cryptTool->stringCompare($calculatedMac, $mac) === true;
    }

    private final function assertIsCapable(string $threemaId, string $wantedCapability)
    {
        $capability = $this->connection->keyCapability($threemaId);
        if (!$capability->can($wantedCapability)) {
            throw new Exception('threema id does not have the capability');
        }
    }

    /**
     * @param ThreemaMessage $message
     * @param string         $blobId blob id as hex
     * @param \Closure       $shouldDownload
     * @return null|\Threema\MsgApi\Commands\Results\DownloadFileResult
     * @throws Exception
     */
    private final function downloadFile(ThreemaMessage $message, $blobId, \Closure $shouldDownload)
    {
        if ($shouldDownload($message, $blobId)) {
            $result = $this->connection->downloadFile($blobId);
            if (null === $result || false === $result->isSuccess()) {
                throw new Exception('could not download the file with blob id ' . $blobId);
            }

            return $result;
        }
        return null;
    }
}