<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\HttpDriver;

use Threema\MsgApi\Commands\CommandInterface;
use Threema\MsgApi\Commands\JsonCommandInterface;
use Threema\MsgApi\Commands\MultiPartCommandInterface;
use Threema\MsgApi\Commands\Results\Response;
use Threema\MsgApi\Constants;
use Threema\MsgApi\Exceptions\HttpException;
use Threema\MsgApi\Helpers\Url;

/**
 */
class CurlHttpDriver implements HttpDriverInterface
{
    public const tlsOptionForceHttps = 'forceHttps';
    public const tlsOptionVersion    = 'tlsVersion';
    public const tlsOptionCipher     = 'tlsCipher';
    public const tlsOptionPinnedKey  = 'pinnedKey';

    public const DEFAULT_HOST = 'https://msgapi.threema.ch';

    /** @var array */
    private $tlsOptions = [];

    /** @var string */
    private $host = self::DEFAULT_HOST;

    /** @var string */
    private $threemaID;

    /** @var string */
    private $apiSecret;

    public function __construct(string $threemaID, string $apiSecret, array $tlsOptions = [])
    {
        $defaults = [self::tlsOptionForceHttps => true,
                     self::tlsOptionVersion    => '1.2',
                     self::tlsOptionCipher     => '',
                     self::tlsOptionPinnedKey  => Constants::DEFAULT_PINNED_KEY];

        $this->tlsOptions = array_merge($defaults, $tlsOptions);
        $this->threemaID  = $threemaID;
        $this->apiSecret  = $apiSecret;
    }

    /**
     * @param CommandInterface $command
     * @param \Closure         $progress
     * @return Response
     */
    public function get(CommandInterface $command, \Closure $progress = null): Response
    {
        return $this->call($command, $this->createDefaultOptions($progress),
            $this->buildRequestParams($command->getParams()));
    }

    /**
     * @param CommandInterface $command
     * @return Response
     */
    public function postForm(CommandInterface $command): Response
    {
        $options = $this->createDefaultOptions();
        $params  = $this->buildRequestParams($command->getParams());

        $options[CURLOPT_POST]       = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($params);
        $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/x-www-form-urlencoded'];

        return $this->call($command, $options, []);
    }

    /**
     * @param MultiPartCommandInterface $command
     * @return Response
     */
    public function postMultiPart(MultiPartCommandInterface $command): Response
    {
        $options = $this->createDefaultOptions();
        $params  = $this->buildRequestParams($command->getParams());

        $options[CURLOPT_POST]        = true;
        $options[CURLOPT_HTTPHEADER]  = ['Content-Type: multipart/form-data'];
        $options[CURLOPT_SAFE_UPLOAD] = true;
        $options[CURLOPT_POSTFIELDS]  = ['blob' => $command->getData()];

        return $this->call($command, $options, $params);
    }

    /**
     * @param JsonCommandInterface $command
     * @return Response
     */
    public function postJson(JsonCommandInterface $command): Response
    {
        $options = $this->createDefaultOptions();
        $params  = $this->buildRequestParams($command->getParams());

        $options[CURLOPT_POST]        = true;
        $options[CURLOPT_HTTPHEADER]  = ['Content-Type: application/json'];
        $options[CURLOPT_SAFE_UPLOAD] = true;
        $options[CURLOPT_POSTFIELDS]  = $command->getJson();

        return $this->call($command, $options, $params);
    }

    /**
     * @param \Closure $progress
     * @return array
     */
    private function createDefaultOptions(\Closure $progress = null): array
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
        ];

        // no progress
        if (null !== $progress) {
            $options[CURLOPT_NOPROGRESS]       = false;
            $options[CURLOPT_PROGRESSFUNCTION] = $progress;
        }

        // tls settings
        if ($this->tlsOptions[self::tlsOptionForceHttps]) {
            //limit allowed protocols to HTTPS
            $options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;
        }

        $tlsVersion = $this->tlsOptions[self::tlsOptionVersion];
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

        $tlsCipher = $this->tlsOptions[self::tlsOptionCipher];
        if (!empty($tlsCipher)) {
            $options[CURLOPT_SSL_CIPHER_LIST] = $tlsCipher;
        }

        $pinnedKey = $this->tlsOptions[self::tlsOptionPinnedKey];
        if (!empty($pinnedKey)) {
            $options[CURLOPT_PINNEDPUBLICKEY] = $pinnedKey;
        }

        return $options;
    }

    /**
     * @param array $params
     * @return array
     */
    private function buildRequestParams(array $params): array
    {
        $params['from']   = $this->threemaID;
        $params['secret'] = $this->apiSecret;
        return $params;
    }

    /**
     * @param CommandInterface $command
     * @param array            $curlOptions
     * @param array            $queryParameters
     * @return Response
     * @throws \Threema\MsgApi\Exceptions\HttpException
     */
    private function call(CommandInterface $command, array $curlOptions, array $queryParameters): Response
    {
        $path     = $command->getPath();
        $fullPath = new Url('', $this->host);
        $fullPath->addPath($path);
        foreach ($queryParameters as $key => $value) {
            $fullPath->setValue($key, $value);
        }
        $session = curl_init($fullPath->getFullPath());
        if (empty($session)) {
            throw new HttpException('Could not start curl session');
        }
        curl_setopt_array($session, $curlOptions);

        $response = curl_exec($session);
        if (false === $response || true === $response) {
            $message = curl_error($session);
            curl_close($session);
            throw new HttpException($path . ' ' . $message);
        }

        $httpCode = intval(curl_getinfo($session, CURLINFO_HTTP_CODE));
        curl_close($session);
        return $command->parseResult($httpCode, $response);
    }
}
