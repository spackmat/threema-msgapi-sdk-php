<?php
declare(strict_types=1);
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi;

use PHPUnit\Framework\TestCase;
use Threema\MsgApi\Helpers\KeyPrefix;

class KeyPrefixTest extends TestCase
{
    public function testGetPrivateKey()
    {
        $realPrivateKey = KeyPrefix::removePrivate(TestConstants::myPrivateKey);
        $this->assertEquals($realPrivateKey, TestConstants::myPrivateKeyExtract, 'getPrivateKey failed');
    }

    public function testGetPublicKey()
    {
        $realPublicKey = KeyPrefix::removePublic(TestConstants::myPublicKey);
        $this->assertEquals($realPublicKey, TestConstants::myPublicKeyExtract, 'myPublicKey failed');
    }

    public function testConvertPrivateKey()
    {
        $p = KeyPrefix::addPrivate('PRIVKEYSTRING');
        $this->assertEquals($p, 'private:PRIVKEYSTRING', 'convertPrivateKey failed');
    }

    public function testConvertPublicKey()
    {
        $p = KeyPrefix::addPublic('PUBKEYSTRING');
        $this->assertEquals($p, 'public:PUBKEYSTRING', 'convertPublicKey failed');
    }
}
