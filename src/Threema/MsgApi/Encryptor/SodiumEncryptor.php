<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Encryptor;

use Threema\MsgApi\Helpers\KeyPair;

/**
 * Contains static methods to do various Threema cryptography related tasks.
 * Support sodium >= 1.0 for php 7.2+
 * @see     https://paragonie.com/book/pecl-libsodium/read/05-publickey-crypto.md
 *
 * @package Threema\Core
 */
class SodiumEncryptor extends AbstractEncryptor
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
     * @return string
     */
    public function getName()
    {
        return 'sodium';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Sodium implementation ' . SODIUM_LIBRARY_VERSION;
    }

    /**
     * Converts a binary string to an hexadecimal string.
     *
     * This is the same as PHP s bin2hex() implementation, but it is resistant to
     * timing attacks.
     *
     * @param  string $binaryString The binary string to convert
     * @return string
     */
    public function bin2hex($binaryString)
    {
        return sodium_bin2hex($binaryString);
    }

    /**
     * Converts an hexadecimal string to a binary string.
     *
     * This is the same as PHP s hex2bin() implementation, but it is resistant to
     * timing attacks.
     *
     * @param  string      $hexString The hex string to convert
     * @param  string|null $ignore    (optional) Characters to ignore
     * @return string
     */
    public function hex2bin($hexString, $ignore = null)
    {
        return sodium_hex2bin($hexString);
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
     * @param string $data
     * @param string $nonce
     * @param string $key
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
     * @return string|null
     */
    protected function openBox($box, $recipientPrivateKey, $senderPublicKey, $nonce)
    {
        $kp = \sodium_crypto_box_keypair_from_secretkey_and_publickey($recipientPrivateKey, $senderPublicKey);
        return \sodium_crypto_box_open($box, $nonce, $kp) ?: null;
    }

    /**
     * decrypt a secret box
     *
     * @param string $box   as binary
     * @param string $nonce as binary
     * @param string $key   as binary
     * @return string|null as binary
     */
    protected function openSecretBox($box, $nonce, $key)
    {
        return \sodium_crypto_secretbox_open($box, $nonce, $key) ?: null;
    }

    /**
     * @param int $size
     * @return string
     * @throws \Exception
     */
    protected function createRandom($size)
    {
        return \random_bytes($size);
    }
}
