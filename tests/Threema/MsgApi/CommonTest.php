<?php
/**
 * @author Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */



namespace Threema\MsgApi;

use PHPUnit\Framework\TestCase;
use Threema\Console\Common;

class CommonTest extends TestCase {

	public function testGetPrivateKey() {
		$realPrivateKey = Common::getPrivateKey(TestConstants::myPrivateKey);
		$this->assertEquals($realPrivateKey, TestConstants::myPrivateKeyExtract, 'getPrivateKey failed');
	}

	public function testGetPublicKey() {
		$realPublicKey = Common::getPublicKey(TestConstants::myPublicKey);
		$this->assertEquals($realPublicKey, TestConstants::myPublicKeyExtract, 'myPublicKey failed');
	}

	public function testConvertPrivateKey() {
		$p = Common::convertPrivateKey('PRIVKEYSTRING');
		$this->assertEquals($p, 'private:PRIVKEYSTRING', 'convertPrivateKey failed');
	}

	public function testConvertPublicKey() {
		$p = Common::convertPublicKey('PUBKEYSTRING');
		$this->assertEquals($p, 'public:PUBKEYSTRING', 'convertPublicKey failed');
	}
}
