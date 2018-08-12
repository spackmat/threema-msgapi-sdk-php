<?php
/**
 * @file
 * @author Lightly Salted Software Ltd
 * Date: 11/08/18
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Threema\Core\KeyPair;
use Threema\MsgApi\Constants;

class EncryptDecryptTest extends TestCase
{
    public function testEncryptDecryptLoop()
    {
        $one = $this->getKeyPair();
        $two = $this->getKeyPair();
        $this->assertNotEquals($one, $two);

        $message = 'Hello world! I have been through the loop';

        $encryptCommand = new CommandTester(new EncryptCommand());
        $encryptCommand->execute(['--private-key' => $one->getPrivateKey(),
                                  'public-key'    => $two->getPublicKey(),
                                  '--message'     => $message]);
        [$nonce, $encrypted] = explode(PHP_EOL, $encryptCommand->getDisplay());

        $decryptCommand = new CommandTester(new DecryptCommand());
        $decryptCommand->execute(['-q',
                                  '--private-key' => $two->getPrivateKey(),
                                  'public-key'    => $one->getPublicKey(),
                                  'nonce'         => $nonce,
                                  '--message'     => $encrypted]);

        $this->assertEquals($message, trim($decryptCommand->getDisplay()));
    }

    private function getKeyPair(): KeyPair
    {
        $keysCommand = new CommandTester(new GenerateKeyPairCommand());
        $keysCommand->execute([]);
        [$private, $public] = explode(PHP_EOL, trim($keysCommand->getDisplay()));
        $this->assertContains(Constants::PRIVATE_KEY_PREFIX, $private);
        $this->assertContains(Constants::PUBLIC_KEY_PREFIX, $public);
        return new KeyPair($private, $public);
    }
}