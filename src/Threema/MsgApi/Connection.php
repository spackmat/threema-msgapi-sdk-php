<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi;

use Threema\Core\Exception;
use Threema\Core\Url;
use Threema\MsgApi\Commands\Capability;
use Threema\MsgApi\Commands\CommandInterface;
use Threema\MsgApi\Commands\Credits;
use Threema\MsgApi\Commands\DownloadFile;
use Threema\MsgApi\Commands\FetchPublicKey;
use Threema\MsgApi\Commands\JsonCommandInterface;
use Threema\MsgApi\Commands\LookupBulk;
use Threema\MsgApi\Commands\LookupEmail;
use Threema\MsgApi\Commands\LookupPhone;
use Threema\MsgApi\Commands\MultiPartCommandInterface;
use Threema\MsgApi\Commands\Results\CapabilityResult;
use Threema\MsgApi\Commands\Results\CreditsResult;
use Threema\MsgApi\Commands\Results\DownloadFileResult;
use Threema\MsgApi\Commands\Results\FetchPublicKeyResult;
use Threema\MsgApi\Commands\Results\LookupBulkResult;
use Threema\MsgApi\Commands\Results\LookupIdResult;
use Threema\MsgApi\Commands\Results\Result;
use Threema\MsgApi\Commands\Results\SendE2EResult;
use Threema\MsgApi\Commands\Results\SendSimpleResult;
use Threema\MsgApi\Commands\Results\UploadFileResult;
use Threema\MsgApi\Commands\SendE2E;
use Threema\MsgApi\Commands\SendSimple;
use Threema\MsgApi\Commands\UploadFile;

/**
 * Class Connection
 * @package Threema\MsgApi
 */
class Connection
{
    const DEFAULT_USE_HTTPS   = true;
    const DEFAULT_TLS_VERSION = '1.2';

    /**
     * @var ConnectionSettings
     */
    private $setting;

    /**
     * @param ConnectionSettings $setting
     */
    public function __construct(ConnectionSettings $setting)
    {
        $this->setting = $setting;
    }

    /**
     * @param Receiver $receiver
     * @param string   $text
     * @return SendSimpleResult
     */
    public function sendSimple(Receiver $receiver, $text): SendSimpleResult
    {
        $command = new SendSimple($receiver, $text);
        return $this->post($command);
    }

    /**
     * @param string $threemaId
     * @param string $nonce
     * @param string $box
     * @return SendE2EResult
     */
    public function sendE2E($threemaId, $nonce, $box): SendE2EResult
    {
        $command = new SendE2E($threemaId, $nonce, $box);
        return $this->post($command);
    }

    /**
     * @param string $encryptedFileData (binary string)
     * @return UploadFileResult
     */
    public function uploadFile($encryptedFileData): UploadFileResult
    {
        $command = new UploadFile($encryptedFileData);
        return $this->postMultiPart($command);
    }

    /**
     * @param string   $blobId
     * @param \Closure $progress
     * @return DownloadFileResult
     */
    public function downloadFile($blobId, \Closure $progress = null): DownloadFileResult
    {
        $command = new DownloadFile($blobId);
        return $this->get($command, $progress);
    }

    /**
     * @param string $phoneNumber
     * @return LookupIdResult
     */
    public function keyLookupByPhoneNumber($phoneNumber): LookupIdResult
    {
        $command = new LookupPhone($phoneNumber);
        return $this->get($command);
    }

    /**
     * @param string $email
     * @return LookupIdResult
     */
    public function keyLookupByEmail(string $email): LookupIdResult
    {
        $command = new LookupEmail($email);
        return $this->get($command);
    }

    /**
     * @param string[] $emailAddresses
     * @param string[] $phoneNumbers
     * @return LookupBulkResult
     */
    public function bulkLookup(array $emailAddresses, array $phoneNumbers): LookupBulkResult
    {
        $command = new LookupBulk($emailAddresses, $phoneNumbers);
        return $this->postJson($command);
    }

    /**
     * @param string $threemaId valid threema id (8 Chars)
     * @return CapabilityResult
     */
    public function keyCapability(string $threemaId): CapabilityResult
    {
        return $this->get(new Capability($threemaId));
    }

    /**
     * @return CreditsResult
     */
    public function credits(): CreditsResult
    {
        return $this->get(new Credits());
    }

    /**
     * @param string $threemaId
     * @return FetchPublicKeyResult
     */
    public function fetchPublicKey(string $threemaId): FetchPublicKeyResult
    {
        return $this->get(new FetchPublicKey($threemaId));
    }

    /**
     * @param CommandInterface $command
     * @param \Closure         $progress
     * @return Result
     * @throws \Threema\Core\Exception
     */
    protected function get(CommandInterface $command, \Closure $progress = null)
    {
        $params = $this->processRequestParams($command->getParams());
        return $this->call($command->getPath(),
            $this->createDefaultOptions($progress),
            $params,
            function ($httpCode, $response) use ($command) {
                return $command->parseResult($httpCode, $response);
            });
    }

    /**
     * @param CommandInterface $command
     * @return Result
     */
    protected function post(CommandInterface $command)
    {
        $options = $this->createDefaultOptions();
        $params  = $this->processRequestParams($command->getParams());

        $options[CURLOPT_POST]       = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($params);
        $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/x-www-form-urlencoded'];

        return $this->call($command->getPath(), $options, null, function ($httpCode, $response) use ($command) {
            return $command->parseResult($httpCode, $response);
        });
    }

    /**
     * @param MultiPartCommandInterface $command
     * @return Result
     */
    protected function postMultiPart(MultiPartCommandInterface $command)
    {
        $options = $this->createDefaultOptions();
        $params  = $this->processRequestParams($command->getParams());

        $options[CURLOPT_POST]        = true;
        $options[CURLOPT_HTTPHEADER]  = ['Content-Type: multipart/form-data'];
        $options[CURLOPT_SAFE_UPLOAD] = true;
        $options[CURLOPT_POSTFIELDS]  = ['blob' => $command->getData()];

        return $this->call($command->getPath(), $options, $params, function ($httpCode, $response) use ($command) {
            return $command->parseResult($httpCode, $response);
        });
    }

    /**
     * @param JsonCommandInterface $command
     * @return Result
     */
    protected function postJson(JsonCommandInterface $command)
    {
        $options = $this->createDefaultOptions();
        $params  = $this->processRequestParams($command->getParams());

        $options[CURLOPT_POST]        = true;
        $options[CURLOPT_HTTPHEADER]  = ['Content-Type: application/json'];
        $options[CURLOPT_SAFE_UPLOAD] = true;
        $options[CURLOPT_POSTFIELDS]  = $command->getJson();

        return $this->call($command->getPath(), $options, $params, function ($httpCode, $response) use ($command) {
            return $command->parseResult($httpCode, $response);
        });
    }

    /**
     * @param \Closure $progress
     * @return array
     */
    private function createDefaultOptions(\Closure $progress = null)
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
        ];

        //no progress
        if (null !== $progress) {
            $options[CURLOPT_NOPROGRESS]       = false;
            $options[CURLOPT_PROGRESSFUNCTION] = $progress;
        }

        //tls settings

        if (true === $this->setting->getTlsOption(ConnectionSettings::tlsOptionForceHttps, self::DEFAULT_USE_HTTPS)) {
            //limit allowed protocols to HTTPS
            $options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;
        }
        if ($tlsVersion = $this->setting->getTlsOption(ConnectionSettings::tlsOptionVersion,
            self::DEFAULT_TLS_VERSION)) {
            if (is_int($tlsVersion)) {
                //if number is given use it
                $options[CURLOPT_SSLVERSION] = $tlsVersion;
            } else {
                //interpret strings as TLS versions
                switch ($tlsVersion) {
                    case '1.0':
                        $options[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_0;
                        break;
                    case '1.1':
                        $options[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_1;
                        break;
                    case '1.2':
                        $options[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;
                        break;
                    default:
                        $options[CURLOPT_SSLVERSION] = CURL_SSLVERSION_DEFAULT;
                        break;
                }
            }
        }
        if ($tlsCipher = $this->setting->getTlsOption(ConnectionSettings::tlsOptionCipher, null)) {
            if (true === is_string($tlsCipher)) {
                $options[CURLOPT_SSL_CIPHER_LIST] = $tlsCipher;
            }
        }
        if ($pinnedKey = $this->setting->getTlsOption(ConnectionSettings::tlsOptionPinnedKey,
            Constants::DEFAULT_PINNED_KEY)) {
            if (true === is_string($pinnedKey)) {
                $options[CURLOPT_PINNEDPUBLICKEY] = $pinnedKey;
            }
        }
        return $options;
    }

    /**
     * @param array $params
     * @return array
     */
    private function processRequestParams(array $params): array
    {
        if (null === $params) {
            $params = [];
        }

        $params['from']   = $this->setting->getThreemaId();
        $params['secret'] = $this->setting->getSecret();

        return $params;
    }

    /**
     * @param string   $path
     * @param array    $curlOptions
     * @param array    $parameters
     * @param \Closure $result
     * @return mixed
     * @throws \Threema\Core\Exception
     */
    private function call($path, array $curlOptions, array $parameters = null, \Closure $result = null)
    {
        $fullPath = new Url('', $this->setting->getHost());
        $fullPath->addPath($path);

        if (null !== $parameters && count($parameters)) {
            foreach ($parameters as $key => $value) {
                $fullPath->setValue($key, $value);
            }
        }
        $session = curl_init($fullPath->getFullPath());
        curl_setopt_array($session, $curlOptions);

        $response = curl_exec($session);
        if (false === $response) {
            throw new Exception($path . ' ' . curl_error($session));
        }

        $httpCode = curl_getinfo($session, CURLINFO_HTTP_CODE);
        if (null === $result && $httpCode != 200) {
            throw new Exception($httpCode);
        }

        if (null !== $result) {
            return $result->__invoke($httpCode, $response);
        } else {
            return $response;
        }
    }
}
