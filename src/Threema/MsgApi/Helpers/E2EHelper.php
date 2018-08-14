<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Helpers;

use Threema\MsgApi\Commands\Results\CapabilityResult;
use Threema\MsgApi\Commands\Results\DownloadFileResult;
use Threema\MsgApi\Commands\Results\SendE2EResult;
use Threema\MsgApi\Connection;
use Threema\MsgApi\Encryptor\AbstractEncryptor;
use Threema\MsgApi\Exceptions\DecryptionFailedException;
use Threema\MsgApi\Exceptions\HttpException;
use Threema\MsgApi\Exceptions\InvalidArgumentException;
use Threema\MsgApi\Exceptions\UnsupportedMessageTypeException;
use Threema\MsgApi\Messages\FileMessage;
use Threema\MsgApi\Messages\ImageMessage;
use Threema\MsgApi\Messages\ThreemaMessage;

/**
 * Splits some of the bulky code out of the Connection class to keep the Connection small / clean
 */
class E2EHelper
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AbstractEncryptor
     */
    private $encryptor;

    /**
     * @var string (binary)
     */
    private $privateKey;

    /**
     * @param string            $privateKeyBinary
     * @param Connection        $connection
     * @param AbstractEncryptor $encryptor
     */
    public function __construct(string $privateKeyBinary, Connection $connection, AbstractEncryptor $encryptor)
    {
        $this->connection = $connection;
        $this->encryptor  = $encryptor;
        $this->privateKey = $privateKeyBinary;
    }

    /**
     * Encrypt a text message and send it to the threemaId
     *
     * @param string $threemaId
     * @param string $receiverPublicKey binary format
     * @param string $text
     * @return \Threema\MsgApi\Commands\Results\SendE2EResult
     * @throws \Threema\MsgApi\Exceptions\Exception
     */
    public final function sendTextMessage(string $threemaId, string $receiverPublicKey, string $text): SendE2EResult
    {
        //random nonce first
        $nonce = $this->encryptor->randomNonce();

        //create a box
        $textMessage = $this->encryptor->encryptMessageText(
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
     * @throws \Threema\MsgApi\Exceptions\Exception
     */
    public final function sendImageMessage(string $threemaId, string $receiverPublicKey,
        string $imagePath): SendE2EResult
    {
        $fileAnalyzeResult = FileAnalysisTool::analyseOrDie($imagePath);

        if (false === in_array($fileAnalyzeResult->getMimeType(), [
                'image/jpg',
                'image/jpeg',
                'image/png'])) {
            throw new InvalidArgumentException('file is not a jpg or png');
        }

        $this->assertIsCapable($threemaId, CapabilityResult::IMAGE);

        //encrypt the image file
        $encryptionResult = $this->encryptor->encryptImage(
            file_get_contents($imagePath) ?: '',
            $this->privateKey,
            $receiverPublicKey);
        $uploadResult     = $this->connection->uploadFile($encryptionResult->getData());
        if (!$uploadResult->isSuccess()) {
            throw new HttpException('could not upload the image (' . $uploadResult->getErrorCode() . ' ' . $uploadResult->getErrorMessage() . ') ' . $uploadResult->getRawResponse());
        }

        $nonce = $this->encryptor->randomNonce();

        //create a image message box
        $imageMessage = $this->encryptor->encryptImageMessage(
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
     * @param string $threemaId
     * @param string $receiverPublicKey binary format
     * @param string $filePath
     * @param string $thumbnailPath
     * @return \Threema\MsgApi\Commands\Results\SendE2EResult
     * @throws \Threema\MsgApi\Exceptions\Exception
     */
    public final function sendFileMessage(string $threemaId, string $receiverPublicKey, string $filePath,
        string $thumbnailPath = ''): SendE2EResult
    {
        $fileAnalyzeResult = FileAnalysisTool::analyseOrDie($filePath);

        $this->assertIsCapable($threemaId, CapabilityResult::FILE);

        //encrypt the main file
        $encryptionResult = $this->encryptor->encryptFile(file_get_contents($filePath) ?: '');
        $uploadResult     = $this->connection->uploadFile($encryptionResult->getData());

        if (!$uploadResult->isSuccess()) {
            throw new HttpException('could not upload the file (' . $uploadResult->getErrorCode() . ' ' . $uploadResult->getErrorMessage() . ') ' . $uploadResult->getRawResponse());
        }

        $thumbnailUploadResult = null;

        //encrypt the thumbnail file (if exists)
        if (!empty($thumbnailPath) && file_exists($thumbnailPath)) {
            $thumbnailEncryptionResult = $this->encryptor->encryptFileThumbnail(
                file_get_contents($thumbnailPath) ?: '',
                $encryptionResult->getKey());
            $thumbnailUploadResult     = $this->connection->uploadFile($thumbnailEncryptionResult->getData());

            if (!$thumbnailUploadResult->isSuccess()) {
                throw new HttpException('could not upload the thumbnail file (' . $thumbnailUploadResult->getErrorCode() . ' ' . $thumbnailUploadResult->getErrorMessage() . ') ' . $thumbnailUploadResult->getRawResponse());
            }
        }

        $nonce = $this->encryptor->randomNonce();

        //create a file message box
        $fileMessage = $this->encryptor->encryptFileMessage(
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
     * @throws \Threema\MsgApi\Exceptions\Exception
     * @throws \Threema\MsgApi\Exceptions\BadMessageException
     * @throws \Threema\MsgApi\Exceptions\DecryptionFailedException
     * @throws \Threema\MsgApi\Exceptions\HttpException
     * @throws \Threema\MsgApi\Exceptions\UnsupportedMessageTypeException
     */
    public final function receiveMessage(
        string $senderPublicKey,
        $messageId,
        $box,
        $nonce,
        $outputFolder = null,
        \Closure $shouldDownload = null): ReceiveMessageResult
    {
        $message = $this->encryptor->decryptMessage(
            $box,
            $this->privateKey,
            $senderPublicKey,
            $nonce
        );

        if (empty($message)) {
            throw new DecryptionFailedException('Could not decrypt box');
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
                $image = $this->encryptor->decryptImage(
                    $result->getData(),
                    $senderPublicKey,
                    $this->privateKey,
                    $message->getNonce()
                );

                if (null === $image) {
                    throw new DecryptionFailedException('decryption of image failed');
                }
                //save file
                $filePath = $outputFolder . '/' . $messageId . '.jpg';
                file_put_contents($filePath, $image);

                $receiveResult->addFile('image', $filePath);
            }
        } else if ($message instanceof FileMessage) {
            $result = $this->downloadFile($message, $message->getBlobId(), $shouldDownload);

            if (null !== $result && true === $result->isSuccess()) {
                $file = $this->encryptor->decryptFile(
                    $result->getData(),
                    $this->encryptor->hex2bin($message->getEncryptionKey()));

                if (null === $file) {
                    throw new DecryptionFailedException('file decryption failed');
                }

                //save file
                $filePath = $outputFolder . '/' . $messageId . '-' . $message->getFilename();
                file_put_contents($filePath, $file);

                $receiveResult->addFile('file', $filePath);
            }

            if (null !== $message->getThumbnailBlobId() && strlen($message->getThumbnailBlobId()) > 0) {
                $result = $this->downloadFile($message, $message->getThumbnailBlobId(), $shouldDownload);
                if (null !== $result && true === $result->isSuccess()) {
                    $file = $this->encryptor->decryptFileThumbnail(
                        $result->getData(),
                        $this->encryptor->hex2bin($message->getEncryptionKey()));

                    if (null === $file) {
                        throw new DecryptionFailedException('thumbnail decryption failed');
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
     * Check the HMAC of an ingoing Threema request. Always do this before decrypting the message.
     *
     * @param string $threemaId
     * @param string $gatewayId
     * @param string $messageId
     * @param string $date
     * @param string $nonce  nonce as hex encoded string
     * @param string $box    box as hex encoded string
     * @param string $mac    the original one send by the server
     * @param string $secret hex
     * @return bool true if check was successful, false if not
     */
    public final function checkMac(string $threemaId, string $gatewayId, string $messageId, string $date, string $nonce,
        string $box, string $mac, string $secret): bool
    {
        $calculatedMac = hash_hmac('sha256', $threemaId . $gatewayId . $messageId . $date . $nonce . $box, $secret);
        return hash_equals($calculatedMac, $mac);
    }

    private function assertIsCapable(string $threemaId, string $wantedCapability)
    {
        $capability = $this->connection->keyCapability($threemaId);
        if (!$capability->can($wantedCapability)) {
            throw new UnsupportedMessageTypeException('threema id does not have the capability');
        }
    }

    /**
     * @param ThreemaMessage $message
     * @param string         $blobId blob id as hex
     * @param \Closure       $shouldDownload
     * @return null|\Threema\MsgApi\Commands\Results\DownloadFileResult
     * @throws \Threema\MsgApi\Exceptions\HttpException
     */
    private function downloadFile(ThreemaMessage $message, $blobId, \Closure $shouldDownload): ?DownloadFileResult
    {
        if ($shouldDownload($message, $blobId)) {
            $result = $this->connection->downloadFile($blobId);
            if (!$result->isSuccess()) {
                throw new HttpException('could not download the file with blob id ' . $blobId);
            }

            return $result;
        }
        return null;
    }
}
