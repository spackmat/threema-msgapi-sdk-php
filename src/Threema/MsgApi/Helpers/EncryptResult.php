<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Helpers;

/**
 * Response of a Data Encryption
 *
 * @package Threema\MsgApi\Tool
 */
class EncryptResult
{
    /**
     * @var string as binary
     */
    private $data;

    /**
     * @var string as binary
     */
    private $key;

    /**
     * @var string as binary
     */
    private $nonce;

    /**
     * @var int
     */
    private $size;

    /**
     * @param string $data  (binary)
     * @param string $key   (binary)
     * @param string $nonce (binary)
     * @param int    $size
     */
    public function __construct(string $data, string $key, string $nonce, int $size)
    {
        $this->data  = $data;
        $this->key   = $key;
        $this->nonce = $nonce;
        $this->size  = $size;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return string (binary)
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string (binary)
     */
    public function getNonce(): string
    {
        return $this->nonce;
    }

    /**
     * @return string (binary)
     */
    public function getData(): string
    {
        return $this->data;
    }
}
