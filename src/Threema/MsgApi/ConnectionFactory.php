<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi;

use Threema\MsgApi\Encryptor\AbstractEncryptor;
use Threema\MsgApi\Encryptor\SodiumEncryptor;
use Threema\MsgApi\HttpDriver\CurlHttpDriver;
use Threema\MsgApi\HttpDriver\HttpDriverInterface;

/**
 * This should be the main way you create new Connections and Encryptors
 * Useful for dependency injection
 */
class ConnectionFactory
{
    /** @var AbstractEncryptor */
    private $encryptor;

    /** @var HttpDriverInterface */
    private $httpDriver;

    /** @var \Threema\MsgApi\Connection */
    private $connection;

    public function __construct(AbstractEncryptor $encryptor = null, HttpDriverInterface $httpDriver = null)
    {
        if (!is_null($encryptor)) {
            $this->encryptor = $encryptor;
        }
        if (!is_null($httpDriver)) {
            $this->httpDriver = $httpDriver;
        }
    }

    /**
     * @return AbstractEncryptor
     */
    public function getEncryptor(): AbstractEncryptor
    {
        // when more encryptors are added, we could check here to see if $encryptor->isSupported() and return them in
        // preference order, or allow setting a preference in the factory or similar.
        // Not needed at the moment: php7.2 and libsodium work well together
        return $this->encryptor ?? $this->encryptor = new SodiumEncryptor();
    }

    /**
     * @param string $threemaID
     * @param string $apiSecret
     * @return \Threema\MsgApi\Connection
     */
    public function getConnection(string $threemaID, string $apiSecret): Connection
    {
        return $this->connection ?? $this->connection = new Connection($this->getHttpDriver($threemaID, $apiSecret),
                $this->getEncryptor());
    }

    /**
     * @param string $threemaID
     * @param string $apiSecret
     * @return HttpDriverInterface
     */
    protected function getHttpDriver(string $threemaID, string $apiSecret): HttpDriverInterface
    {
        // when more drivers are added (eg Guzzle), we need a way for the user to set a preference
        return $this->httpDriver ?? $this->httpDriver = new CurlHttpDriver($threemaID, $apiSecret);
    }
}