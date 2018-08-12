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
use Threema\MsgApi\Constants;

class DerivePublicKeyCommandTest extends TestCase
{
    public function testRun()
    {
        $keysCommand = new CommandTester(new GenerateKeyPairCommand());
        $keysCommand->execute([]);
        [$private, $public] = explode(PHP_EOL, trim($keysCommand->getDisplay()));

        $deriveCommand = new CommandTester(new DerivePublicKeyCommand());
        $deriveCommand->execute(['--private-key' => $private]);
        $this->assertEquals(str_replace(Constants::PUBLIC_KEY_PREFIX, '', $public), trim($deriveCommand->getDisplay()));
    }
}
