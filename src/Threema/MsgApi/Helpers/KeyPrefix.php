<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Helpers;

class KeyPrefix
{
    /**
     * Append the prefix to the the PublicKey
     *
     * @param string $keyHex PublicKey in hex
     * @return string
     */
    public static function addPublic(string $keyHex): string
    {
        return Constants::PUBLIC_KEY_PREFIX . $keyHex;
    }

    /**
     * Extract the PublicKey
     *
     * @param string $keyHexWithPrefix PublicKey in hex with the key-prefix
     * @return string
     */
    public static function removePublic(string $keyHexWithPrefix): string
    {
        return str_replace(Constants::PUBLIC_KEY_PREFIX, '', $keyHexWithPrefix);
    }

    /**
     * Append the prefix to the the PrivateKey @key
     *
     * @param string $keyHex PrivateKey in hex
     * @return string
     */
    public static function addPrivate(string $keyHex): string
    {
        return Constants::PRIVATE_KEY_PREFIX . $keyHex;
    }

    /**
     * Extract the PrivateKey
     *
     * @param string $keyHexWithPrefix PrivateKey in hex with the key-prefix
     * @return string
     */
    public static function removePrivate(string $keyHexWithPrefix): string
    {
        return str_replace(Constants::PRIVATE_KEY_PREFIX, '', $keyHexWithPrefix);
    }
}
