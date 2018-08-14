<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * Date: 11/08/18
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Threema\MsgApi\ConnectionFactory;
use Threema\MsgApi\Constants;
use Threema\MsgApi\Helpers\KeyPair;

class EncryptDecryptTest extends TestCase
{
    public function testEncryptDecryptLoop()
    {
        $connectionFactory = new ConnectionFactory();

        $one = $this->getKeyPair($connectionFactory);
        $two = $this->getKeyPair($connectionFactory);
        $this->assertNotEquals($one, $two);

        $message = 'Hello world! I have been through the loop';

        $encryptCommand = new CommandTester(new EncryptCommand($connectionFactory));
        $encryptCommand->execute(['--private-key' => $one->getPrivateKey(),
                                  'public-key'    => $two->getPublicKey(),
                                  '--message'     => $message]);
        [$nonce, $encrypted] = explode(PHP_EOL, $encryptCommand->getDisplay());

        $decryptCommand = new CommandTester(new DecryptCommand($connectionFactory));
        $decryptCommand->execute(['-q',
                                  '--private-key' => $two->getPrivateKey(),
                                  'public-key'    => $one->getPublicKey(),
                                  'nonce'         => $nonce,
                                  '--message'     => $encrypted]);

        $this->assertEquals($message, trim($decryptCommand->getDisplay()));
    }

    private function getKeyPair(ConnectionFactory $connectionFactory): KeyPair
    {
        $keysCommand = new CommandTester(new GenerateKeyPairCommand($connectionFactory));
        $keysCommand->execute([]);
        [$private, $public] = explode(PHP_EOL, trim($keysCommand->getDisplay()));
        $this->assertContains(Constants::PRIVATE_KEY_PREFIX, $private);
        $this->assertContains(Constants::PUBLIC_KEY_PREFIX, $public);
        return new KeyPair($private, $public);
    }
}
