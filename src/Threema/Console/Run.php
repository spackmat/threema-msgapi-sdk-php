<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\Console;

use Threema\Console\Command\Base;
use Threema\Console\Command\Capability;
use Threema\Console\Command\Credits;
use Threema\Console\Command\Decrypt;
use Threema\Console\Command\DerivePublicKey;
use Threema\Console\Command\Encrypt;
use Threema\Console\Command\FeatureLevel;
use Threema\Console\Command\GenerateKeyPair;
use Threema\Console\Command\HashEmail;
use Threema\Console\Command\HashPhone;
use Threema\Console\Command\LookupBulk;
use Threema\Console\Command\LookupIdByEmail;
use Threema\Console\Command\LookupIdByPhoneNo;
use Threema\Console\Command\LookupPublicKeyById;
use Threema\Console\Command\ReceiveMessage;
use Threema\Console\Command\SendE2EFile;
use Threema\Console\Command\SendE2EImage;
use Threema\Console\Command\SendE2EText;
use Threema\Console\Command\SendSimple;
use Threema\Core\Exception;
use Threema\MsgApi\Constants;
use Threema\MsgApi\Tools\CryptTool;

/**
 * Handling the console run stuff
 *
 * @package Threema\Console
 */
class Run
{
    /**
     * @var array
     */
    private $arguments = [];

    /**
     * @var \Threema\Console\Command\Base[]
     */
    private $commands = [];

    /**
     * @var string
     */
    private $scriptName;

    /**
     * @param array $arguments
     * @throws \Threema\Core\Exception
     */
    public function __construct(array $arguments)
    {
        $this->arguments      = $arguments;
        $this->scriptName     = basename(array_shift($this->arguments));

        $this->registerSubject('Local operations (no network communication)');
        $this->register('-e', new Encrypt());
        $this->register('-D', new Decrypt());
        $this->register(['-h', '-e'], new HashEmail());
        $this->register(['-h', '-p'], new HashPhone());
        $this->register('-g', new GenerateKeyPair());
        $this->register('-d', new DerivePublicKey());
        $this->register('-v', new FeatureLevel());

        $this->registerSubject('Network operations');
        //network operations
        $this->register('-s', new SendSimple());
        $this->register('-S', new SendE2EText());
        $this->register(['-S', '-i'], new SendE2EImage());
        $this->register(['-S', '-f'], new SendE2EFile());
        $this->register(['-l', '-e'], new LookupIdByEmail());
        $this->register(['-l', '-p'], new LookupIdByPhoneNo());
        $this->register(['-l', '-k'], new LookupPublicKeyById());
        $this->register(['-l', '-b'], new LookupBulk());
        $this->register(['-c'], new Capability());
        $this->register(['-r'], new ReceiveMessage());
        $this->register(['-C'], new Credits());
    }

    public function run()
    {
        $found          = null;
        $argumentLength = 0;

        //find the correct command by arguments and arguments count
        foreach ($this->commands as $data) {
            if (is_scalar($data)) {
                continue;
            }

            list($keys, $command) = $data;
            if (array_slice($this->arguments, 0, count($keys)) == $keys) {
                $argCount = count($this->arguments) - count($keys);

                /** @noinspection PhpUndefinedMethodInspection */
                if ($argCount >= $command->getRequiredArgumentCount()
                    && $argCount <= $command->getAllArgumentsCount()) {
                    $found          = $command;
                    $argumentLength = count($keys);
                    break;
                }
            }
        }

        if ($argumentLength > 0) {
            array_splice($this->arguments, 0, $argumentLength);
        }

        if (null === $found) {
            $this->help();
        } else {

            try {
                $found->run($this->arguments);
            } catch (Exception $x) {
                Common::l();
                Common::e('ERROR: ' . $x->getMessage());
                Common::e(get_class($x));
                Common::l();
            }
        }
    }

    public function writeHelp(\Closure $writer)
    {
        if (null !== $writer) {

            foreach ($this->commands as $data) {
                if (is_scalar($data)) {
                    $writer->__invoke($data, null, null, false);
                } else {
                    list($key, $command) = $data;
                    $writer->__invoke($command->subject(false),
                        $this->scriptName . ' ' . implode(' ', $key) . ' ' . $command->help(false),
                        $command->description(), true);
                }
            }
        }
    }

    private function register($argumentKey, Base $command)
    {
        if (is_scalar($argumentKey)) {
            $argumentKey = [$argumentKey];
        }

        //check for existing commands with the same arguments
        foreach ($this->commands as $commandValues) {
            $ex = $commandValues[0];
            if (null !== $ex && is_array($ex)) {
                if (count($ex) == count($argumentKey)
                    && count(array_diff($ex, $argumentKey)) == 0) {
                    throw new Exception('arguments ' . implode($argumentKey) . ' already used');
                }
            }
        }
        $this->commands[] = [$argumentKey, $command];
        return $this;
    }

    private function registerSubject($name)
    {
        $this->commands[] = $name;
    }

    private function help()
    {
        $defaultCryptTool = CryptTool::getInstance();

//		Common::l();
        Common::l('Threema PHP MsgApi Tool');
        Common::l('Version: ' . Constants::MSGAPI_SDK_VERSION);
        Common::l('Feature level: ' . Constants::MSGAPI_SDK_FEATURE_LEVEL);
        Common::l('CryptTool: ' . $defaultCryptTool->getName() . ' (' . $defaultCryptTool->getDescription() . ')');
        Common::l(str_repeat('.', 40));
        Common::l();
        foreach ($this->commands as $data) {
            if (is_scalar($data)) {
                Common::l($data);
                Common::l(str_repeat('-', strlen($data)));
                Common::l();
            } else {
                list($key, $command) = $data;
                Common::ln($this->scriptName . ' ' . "\033[1;33m" . implode(' ',
                        $key) . "\033[0m" . ' ' . $command->help());
                Common::l();
                /** @noinspection PhpUndefinedMethodInspection */
                Common::l($command->description(), 1);
                Common::l();
            }
        }
    }
}
