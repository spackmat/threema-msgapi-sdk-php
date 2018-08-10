<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi;

class ConnectionSettings
{
    const tlsOptionForceHttps = 'forceHttps';
    const tlsOptionVersion    = 'tlsVersion';
    const tlsOptionCipher     = 'tlsCipher';
    const tlsOptionPinnedKey  = 'pinnedKey';

    /**
     * @var string
     */
    private $threemaId;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $host;

    /**
     * @var array
     */
    private $tlsOptions = [];

    /**
     * @param string      $threemaId  valid threema id (8chars)
     * @param string      $secret     secret
     * @param string|null $host       server url
     * @param array|null  $tlsOptions advanced TLS options
     */
    public function __construct(string $threemaId, string $secret, ?string $host = null, array $tlsOptions = null)
    {
        $this->threemaId = $threemaId;
        $this->secret    = $secret;
        $this->host      = $host ?? 'https://msgapi.threema.ch';

        // TLS options
        if (null !== $tlsOptions && is_array($tlsOptions)) {
            if (true === array_key_exists(self::tlsOptionForceHttps, $tlsOptions)) {
                $this->tlsOptions[self::tlsOptionForceHttps] = $tlsOptions[self::tlsOptionForceHttps] === true;
            }

            if (true === array_key_exists(self::tlsOptionVersion, $tlsOptions)) {
                $this->tlsOptions[self::tlsOptionVersion] = $tlsOptions[self::tlsOptionVersion];
            }

            if (true === array_key_exists(self::tlsOptionCipher, $tlsOptions)) {
                $this->tlsOptions[self::tlsOptionCipher] = $tlsOptions[self::tlsOptionCipher];
            }

            if (true === array_key_exists(self::tlsOptionPinnedKey, $tlsOptions)) {
                $this->tlsOptions[self::tlsOptionPinnedKey] = $tlsOptions[self::tlsOptionPinnedKey];
            }
        }
    }

    /**
     * @return string
     */
    public function getThreemaId(): string
    {
        return $this->threemaId;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return array
     */
    public function getTlsOptions(): array
    {
        return $this->tlsOptions;
    }

    /**
     * @param string           $option
     * @param string|bool|null $default
     * @return string|bool
     */
    public function getTlsOption(string $option, $default = null)
    {
        return true === array_key_exists($option, $this->tlsOptions) ? $this->tlsOptions[$option] : $default;
    }
}
