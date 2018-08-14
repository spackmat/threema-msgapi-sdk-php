<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi;

use Threema\MsgApi\Commands\Capability;
use Threema\MsgApi\Commands\Credits;
use Threema\MsgApi\Commands\DownloadFile;
use Threema\MsgApi\Commands\FetchPublicKey;
use Threema\MsgApi\Commands\LookupBulk;
use Threema\MsgApi\Commands\LookupEmail;
use Threema\MsgApi\Commands\LookupPhone;
use Threema\MsgApi\Commands\SendE2E;
use Threema\MsgApi\Commands\SendSimple;
use Threema\MsgApi\Commands\UploadFile;
use Threema\MsgApi\Encryptor\AbstractEncryptor;
use Threema\MsgApi\Helpers\E2EHelper;
use Threema\MsgApi\Helpers\ReceiveMessageResult;
use Threema\MsgApi\HttpDriver\HttpDriverInterface;
use Threema\MsgApi\Response\CapabilityResponse;
use Threema\MsgApi\Response\CreditsResponse;
use Threema\MsgApi\Response\DownloadFileResponse;
use Threema\MsgApi\Response\FetchPublicKeyResponse;
use Threema\MsgApi\Response\LookupBulkResponse;
use Threema\MsgApi\Response\LookupIdResponse;
use Threema\MsgApi\Response\SendE2EResponse;
use Threema\MsgApi\Response\SendSimpleResponse;
use Threema\MsgApi\Response\UploadFileResponse;

/**
 * talk to the Threema Gateway server via the HttpDriverInterface
 */
class Connection
{
    /** @var \Threema\MsgApi\HttpDriver\HttpDriverInterface */
    protected $driver;

    /** @var \Threema\MsgApi\Encryptor\AbstractEncryptor */
    protected $encryptor;

    public function __construct(HttpDriverInterface $driver, AbstractEncryptor $encryptor)
    {
        $this->driver    = $driver;
        $this->encryptor = $encryptor;
    }

    public function sendToThreemaID(string $threemaID, string $text): SendSimpleResponse
    {
        $result = $this->driver->postForm(SendSimple::toThreemaID($threemaID, $text));
        assert($result instanceof SendSimpleResponse);
        return $result;
    }

    public function sendToEmail(string $email, string $text): SendSimpleResponse
    {
        $result = $this->driver->postForm(SendSimple::toEmail($email, $text));
        assert($result instanceof SendSimpleResponse);
        return $result;
    }

    public function sendToPhoneNo(string $phoneNo, string $text): SendSimpleResponse
    {
        $result = $this->driver->postForm(SendSimple::toPhoneNo($phoneNo, $text));
        assert($result instanceof SendSimpleResponse);
        return $result;
    }

    /**
     * @param string $threemaId
     * @param string $nonce binary
     * @param string $box   binary
     * @return SendE2EResponse
     */
    public function sendE2E(string $threemaId, string $nonce, string $box): SendE2EResponse
    {
        $command = new SendE2E($threemaId, $this->encryptor->bin2hex($nonce), $this->encryptor->bin2hex($box));
        $result  = $this->driver->postForm($command);
        assert($result instanceof SendE2EResponse);
        return $result;
    }

    /**
     * @param string $encryptedFileData (binary string)
     * @return UploadFileResponse
     */
    public function uploadFile($encryptedFileData): UploadFileResponse
    {
        $result = $this->driver->postMultiPart(new UploadFile($encryptedFileData));
        assert($result instanceof UploadFileResponse);
        return $result;
    }

    /**
     * @param string   $blobId
     * @param \Closure $progress
     * @return DownloadFileResponse
     */
    public function downloadFile($blobId, \Closure $progress = null): DownloadFileResponse
    {
        $result = $this->driver->get(new DownloadFile($blobId), $progress);
        assert($result instanceof DownloadFileResponse);
        return $result;
    }

    /**
     * @param string $phoneNumber
     * @return LookupIdResponse
     */
    public function keyLookupByPhoneNumber(string $phoneNumber): LookupIdResponse
    {
        $result = $this->driver->get(new LookupPhone($phoneNumber, $this->encryptor->hashPhoneNo($phoneNumber)));
        assert($result instanceof LookupIdResponse);
        return $result;
    }

    /**
     * @param string $email
     * @return LookupIdResponse
     */
    public function keyLookupByEmail(string $email): LookupIdResponse
    {
        $result = $this->driver->get(new LookupEmail($email, $this->encryptor->hashEmail($email)));
        assert($result instanceof LookupIdResponse);
        return $result;
    }

    /**
     * @param string[] $emailAddresses
     * @param string[] $phoneNumbers
     * @return LookupBulkResponse
     */
    public function bulkLookup(array $emailAddresses, array $phoneNumbers): LookupBulkResponse
    {
        $command = new LookupBulk($emailAddresses, $phoneNumbers);
        $command->calculateHashes($this->encryptor);
        $result = $this->driver->postJson($command);
        assert($result instanceof LookupBulkResponse);
        return $result;
    }

    /**
     * @param string $threemaId valid threema id (8 Chars)
     * @return CapabilityResponse
     */
    public function keyCapability(string $threemaId): CapabilityResponse
    {
        $result = $this->driver->get(new Capability($threemaId));
        assert($result instanceof CapabilityResponse);
        return $result;
    }

    /**
     * @return CreditsResponse
     */
    public function credits(): CreditsResponse
    {
        $result = $this->driver->get(new Credits());
        assert($result instanceof CreditsResponse);
        return $result;
    }

    /**
     * @param string $threemaId
     * @return FetchPublicKeyResponse
     */
    public function fetchPublicKey(string $threemaId): FetchPublicKeyResponse
    {
        $result = $this->driver->get(new FetchPublicKey($threemaId));
        assert($result instanceof FetchPublicKeyResponse);
        return $result;
    }

    /**
     * Encrypt a text message and send it to the threemaId
     *
     * @param string $myPrivateKeyHex
     * @param string $toThreemaId
     * @param string $toPublicKeyHex
     * @param string $text
     * @return \Threema\MsgApi\Response\SendE2EResponse
     * @throws \Threema\MsgApi\Exceptions\Exception
     */
    public function sendTextMessage(string $myPrivateKeyHex, string $toThreemaId, string $toPublicKeyHex,
        string $text): SendE2EResponse
    {
        return $this->getE2EHelper($myPrivateKeyHex)
                    ->sendTextMessage($toThreemaId, $this->encryptor->hex2bin($toPublicKeyHex), $text);
    }

    /**
     * Encrypt an image file, upload the blob and send the image message to the threemaId
     *
     * @param string $myPrivateKeyHex
     * @param string $toThreemaId
     * @param string $toPublicKeyHex
     * @param string $imagePath
     * @return \Threema\MsgApi\Response\SendE2EResponse
     * @throws \Threema\MsgApi\Exceptions\Exception
     */
    public function sendImageMessage(string $myPrivateKeyHex, string $toThreemaId, string $toPublicKeyHex,
        string $imagePath): SendE2EResponse
    {
        return $this->getE2EHelper($myPrivateKeyHex)
                    ->sendImageMessage($toThreemaId, $this->encryptor->hex2bin($toPublicKeyHex), $imagePath);
    }

    /**
     * Encrypt a file (and thumbnail if given), upload the blob and send it to the given threemaId
     *
     * @param string $myPrivateKeyHex
     * @param string $toThreemaId
     * @param string $toPublicKeyHex
     * @param string $filePath
     * @param string $thumbnailPath
     * @return \Threema\MsgApi\Response\SendE2EResponse
     * @throws \Threema\MsgApi\Exceptions\Exception
     */
    public final function sendFileMessage(string $myPrivateKeyHex, string $toThreemaId, string $toPublicKeyHex,
        string $filePath, string $thumbnailPath = '')
    {
        return $this->getE2EHelper($myPrivateKeyHex)
                    ->sendFileMessage($toThreemaId, $this->encryptor->hex2bin($toPublicKeyHex), $filePath,
                        $thumbnailPath);
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
    public final function macIsValid(string $threemaId, string $gatewayId, string $messageId, string $date,
        string $nonce, string $box, string $mac, string $secret): bool
    {
        $calculatedMac = hash_hmac('sha256', $threemaId . $gatewayId . $messageId . $date . $nonce . $box, $secret);
        return hash_equals($calculatedMac, $mac);
    }

    /**
     * Decrypt a message and optionally download the files of the message to the $outputFolder
     *
     * Note: This does not check the MAC before, which you should always do when
     * you want to use this in your own application! @see macIsValid()
     *
     * @param string            $myPrivateKeyHex
     * @param string            $senderPublicKeyHex
     * @param string            $messageId
     * @param string            $boxHex
     * @param string            $nonceHex
     * @param string|null|false $outputFolder      folder for storing the files,
     *                                             null=current folder, false=do not download files
     * @param \Closure          $shouldDownload
     * @return \Threema\MsgApi\Helpers\ReceiveMessageResult
     * @throws \Threema\MsgApi\Exceptions\Exception
     * @throws \Threema\MsgApi\Exceptions\BadMessageException
     * @throws \Threema\MsgApi\Exceptions\DecryptionFailedException
     * @throws \Threema\MsgApi\Exceptions\HttpException
     * @throws \Threema\MsgApi\Exceptions\UnsupportedMessageTypeException
     */
    public final function receiveMessage(string $myPrivateKeyHex, string $senderPublicKeyHex, string $messageId,
        string $boxHex, string $nonceHex, $outputFolder = null, \Closure $shouldDownload = null): ReceiveMessageResult
    {
        return $this->getE2EHelper($myPrivateKeyHex)
                    ->receiveMessage($this->encryptor->hex2bin($senderPublicKeyHex), $messageId,
                        $this->encryptor->hex2bin($boxHex), $this->encryptor->hex2bin($nonceHex), $outputFolder,
                        $shouldDownload);
    }

    protected function getE2EHelper(string $myPrivateKeyHex): E2EHelper
    {
        return new E2EHelper($this->encryptor->hex2bin($myPrivateKeyHex), $this, $this->encryptor);
    }
}
