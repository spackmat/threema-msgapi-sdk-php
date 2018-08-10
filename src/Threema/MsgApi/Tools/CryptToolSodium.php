<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Tools;

use Threema\Core\Exception;
use Threema\Core\KeyPair;

/**
 * Contains static methods to do various Threema cryptography related tasks.
 * Support sodium >= 1.0 for php 7.2+
 * @see     https://paragonie.com/book/pecl-libsodium/read/05-publickey-crypto.md
 *
 * @package Threema\Core
 */
class CryptToolSodium extends CryptTool
{
    /**
     * Generate a new key pair.
     *
     * @return KeyPair the new key pair
     */
    final public function generateKeyPair()
    {
        $kp = \sodium_crypto_box_keypair();
        return new KeyPair(\sodium_crypto_box_secretkey($kp), \sodium_crypto_box_publickey($kp));
    }

    /**
     * Derive the public key
     *
     * @param string $privateKey in binary
     * @return string public key as binary
     */
    final public function derivePublicKey($privateKey)
    {
        return \sodium_crypto_box_publickey_from_secretkey($privateKey);
    }

    /**
     * Check if implementation supported
     * @return bool
     */
    public function isSupported()
    {
        if (!extension_loaded("sodium")) {
            return false;
        }
        // check any function from new libsodium without namespaces
        return function_exists('sodium_crypto_box');
    }

    /**
     * Validate crypt tool
     *
     * @return bool
     * @throws Exception
     */
    public function validate()
    {
        if (false === $this->isSupported()) {
            throw new Exception('Sodium implementation not supported');
        }
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sodium';
    }

    /**
     * Description of the CryptTool
     * @return string
     */
    public function getDescription()
    {
        return 'Sodium implementation ' . SODIUM_LIBRARY_VERSION;
    }

    /**
     * @param string $data
     * @param string $nonce
     * @param string $senderPrivateKey
     * @param string $recipientPublicKey
     * @return string encrypted box
     */
    protected function makeBox($data, $nonce, $senderPrivateKey, $recipientPublicKey)
    {
        $kp = \sodium_crypto_box_keypair_from_secretkey_and_publickey($senderPrivateKey, $recipientPublicKey);

        return \sodium_crypto_box($data, $nonce, $kp);
    }

    /**
     * make a secret box
     *
     * @param $data
     * @param $nonce
     * @param $key
     * @return mixed
     */
    protected function makeSecretBox($data, $nonce, $key)
    {
        return \sodium_crypto_secretbox($data, $nonce, $key);
    }

    /**
     * @param string $box
     * @param string $recipientPrivateKey
     * @param string $senderPublicKey
     * @param string $nonce
     * @return null|string
     */
    protected function openBox($box, $recipientPrivateKey, $senderPublicKey, $nonce)
    {
        $kp = \sodium_crypto_box_keypair_from_secretkey_and_publickey($recipientPrivateKey, $senderPublicKey);
        return \sodium_crypto_box_open($box, $nonce, $kp);
    }

    /**
     * decrypt a secret box
     *
     * @param string $box   as binary
     * @param string $nonce as binary
     * @param string $key   as binary
     * @return string as binary
     */
    protected function openSecretBox($box, $nonce, $key)
    {
        return \sodium_crypto_secretbox_open($box, $nonce, $key);
    }

    /**
     * @param int $size
     * @return string
     * @throws \Exception
     */
    protected function createRandom($size)
    {
        return \random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
    }
}
