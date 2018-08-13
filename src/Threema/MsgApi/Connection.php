<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi;

use Threema\MsgApi\Commands\Capability;
use Threema\MsgApi\Commands\Credits;
use Threema\MsgApi\Commands\DownloadFile;
use Threema\MsgApi\Commands\FetchPublicKey;
use Threema\MsgApi\Commands\LookupBulk;
use Threema\MsgApi\Commands\LookupEmail;
use Threema\MsgApi\Commands\LookupPhone;
use Threema\MsgApi\Commands\Results\CapabilityResult;
use Threema\MsgApi\Commands\Results\CreditsResult;
use Threema\MsgApi\Commands\Results\DownloadFileResult;
use Threema\MsgApi\Commands\Results\FetchPublicKeyResult;
use Threema\MsgApi\Commands\Results\LookupBulkResult;
use Threema\MsgApi\Commands\Results\LookupIdResult;
use Threema\MsgApi\Commands\Results\SendE2EResult;
use Threema\MsgApi\Commands\Results\SendSimpleResult;
use Threema\MsgApi\Commands\Results\UploadFileResult;
use Threema\MsgApi\Commands\SendE2E;
use Threema\MsgApi\Commands\SendSimple;
use Threema\MsgApi\Commands\UploadFile;
use Threema\MsgApi\Encryptor\AbstractEncryptor;
use Threema\MsgApi\Helpers\E2EHelper;
use Threema\MsgApi\Helpers\ReceiveMessageResult;
use Threema\MsgApi\HttpDriver\HttpDriverInterface;

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

    /**
     * @param Receiver $receiver
     * @param string   $text
     * @return SendSimpleResult
     */
    public function sendSimple(Receiver $receiver, string $text): SendSimpleResult
    {
        $result = $this->driver->postForm(new SendSimple($receiver, $text));
        assert($result instanceof SendSimpleResult);
        return $result;
    }

    /**
     * @param string $threemaId
     * @param string $nonce binary
     * @param string $box   binary
     * @return SendE2EResult
     */
    public function sendE2E(string $threemaId, string $nonce, string $box): SendE2EResult
    {
        $command = new SendE2E($threemaId, $this->encryptor->bin2hex($nonce), $this->encryptor->bin2hex($box));
        $result  = $this->driver->postForm($command);
        assert($result instanceof SendE2EResult);
        return $result;
    }

    /**
     * @param string $encryptedFileData (binary string)
     * @return UploadFileResult
     */
    public function uploadFile($encryptedFileData): UploadFileResult
    {
        $result = $this->driver->postMultiPart(new UploadFile($encryptedFileData));
        assert($result instanceof UploadFileResult);
        return $result;
    }

    /**
     * @param string   $blobId
     * @param \Closure $progress
     * @return DownloadFileResult
     */
    public function downloadFile($blobId, \Closure $progress = null): DownloadFileResult
    {
        $result = $this->driver->get(new DownloadFile($blobId), $progress);
        assert($result instanceof DownloadFileResult);
        return $result;
    }

    /**
     * @param string $phoneNumber
     * @return LookupIdResult
     */
    public function keyLookupByPhoneNumber(string $phoneNumber): LookupIdResult
    {
        $result = $this->driver->get(new LookupPhone($phoneNumber, $this->encryptor->hashPhoneNo($phoneNumber)));
        assert($result instanceof LookupIdResult);
        return $result;
    }

    /**
     * @param string $email
     * @return LookupIdResult
     */
    public function keyLookupByEmail(string $email): LookupIdResult
    {
        $result = $this->driver->get(new LookupEmail($email, $this->encryptor->hashEmail($email)));
        assert($result instanceof LookupIdResult);
        return $result;
    }

    /**
     * @param string[] $emailAddresses
     * @param string[] $phoneNumbers
     * @return LookupBulkResult
     */
    public function bulkLookup(array $emailAddresses, array $phoneNumbers): LookupBulkResult
    {
        $command = new LookupBulk($emailAddresses, $phoneNumbers);
        $command->calculateHashes($this->encryptor);
        $result = $this->driver->postJson($command);
        assert($result instanceof LookupBulkResult);
        return $result;
    }

    /**
     * @param string $threemaId valid threema id (8 Chars)
     * @return CapabilityResult
     */
    public function keyCapability(string $threemaId): CapabilityResult
    {
        $result = $this->driver->get(new Capability($threemaId));
        assert($result instanceof CapabilityResult);
        return $result;
    }

    /**
     * @return CreditsResult
     */
    public function credits(): CreditsResult
    {
        $result = $this->driver->get(new Credits());
        assert($result instanceof CreditsResult);
        return $result;
    }

    /**
     * @param string $threemaId
     * @return FetchPublicKeyResult
     */
    public function fetchPublicKey(string $threemaId): FetchPublicKeyResult
    {
        $result = $this->driver->get(new FetchPublicKey($threemaId));
        assert($result instanceof FetchPublicKeyResult);
        return $result;
    }

    /**
     * Encrypt a text message and send it to the threemaId
     *
     * @param string $myPrivateKeyHex
     * @param string $toThreemaId
     * @param string $toPublicKeyHex
     * @param string $text
     * @return \Threema\MsgApi\Commands\Results\SendE2EResult
     * @throws \Threema\Core\Exception
     */
    public function sendTextMessage(string $myPrivateKeyHex, string $toThreemaId, string $toPublicKeyHex,
        string $text): SendE2EResult
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
     * @return \Threema\MsgApi\Commands\Results\SendE2EResult
     * @throws \Threema\Core\Exception
     */
    public function sendImageMessage(string $myPrivateKeyHex, string $toThreemaId, string $toPublicKeyHex,
        string $imagePath): SendE2EResult
    {
        return $this->getE2EHelper($myPrivateKeyHex)
                    ->sendImageMessage($toThreemaId, $this->encryptor->hex2bin($toPublicKeyHex), $imagePath);
    }

    /**
     * Encrypt a file (and thumbnail if given), upload the blob and send it to the given threemaId
     *
     * @param string      $myPrivateKeyHex
     * @param string      $toThreemaId
     * @param string      $toPublicKeyHex
     * @param string      $filePath
     * @param null|string $thumbnailPath
     * @return \Threema\MsgApi\Commands\Results\SendE2EResult
     * @throws \Threema\Core\Exception
     */
    public final function sendFileMessage(string $myPrivateKeyHex, string $toThreemaId, string $toPublicKeyHex,
        string $filePath, ?string $thumbnailPath = null)
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
     * @throws \Threema\Core\Exception
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
