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
use Threema\MsgApi\HttpDriver\HttpDriverInterface;

/**
 * Class Connection
 * @package Threema\MsgApi
 */
class Connection
{
    /** @var \Threema\MsgApi\HttpDriver\HttpDriverInterface */
    private $driver;

    public function __construct(HttpDriverInterface $driver)
    {
        $this->driver = $driver;
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
    public function sendE2E($threemaId, $nonce, $box): SendE2EResult
    {
        $result = $this->driver->postForm(new SendE2E($threemaId, $nonce, $box));
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
    public function keyLookupByPhoneNumber($phoneNumber): LookupIdResult
    {
        $result = $this->driver->get(new LookupPhone($phoneNumber));
        assert($result instanceof LookupIdResult);
        return $result;
    }

    /**
     * @param string $email
     * @return LookupIdResult
     */
    public function keyLookupByEmail(string $email): LookupIdResult
    {
        $result = $this->driver->get(new LookupEmail($email));
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
        $result = $this->driver->postJson(new LookupBulk($emailAddresses, $phoneNumbers));
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
}
