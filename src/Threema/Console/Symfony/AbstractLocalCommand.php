<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Threema\MsgApi\Constants;
use Threema\MsgApi\Exceptions\InvalidArgumentException;
use Threema\MsgApi\Tools\CryptTool;

abstract class AbstractLocalCommand extends Command
{
    private const KEY_FILE = 'default.key';

    /** @var array default option and argument values stored in self::KEY_FILE */
    private $defaults = [];

    protected function configure()
    {
        parent::configure();
        $this->addOption('key-file', 'k', InputOption::VALUE_REQUIRED,
            'Name of file (in .ini format) that holds your threema id, public and private keys etc',
            self::KEY_FILE);
    }

    protected function requirePrivateKey()
    {
        $this->addOption('private-key', 'P', InputOption::VALUE_OPTIONAL,
            'Sender private key either as a hex on the command line or name of file in the current working directory. ' .
            'The file should have a single line containing the key value in hex', '');
        return $this;
    }

    protected function requirePublicKey()
    {
        $this->addArgument('public-key', InputArgument::REQUIRED,
            'Recipient public key either as a hex on the command line or name of file in the current working directory. ' .
            'The file should have a single line containing the key value in hex');
        return $this;
    }

    protected function optionalMessageOrStdIn()
    {
        $this->addOption('message', 'm', InputOption::VALUE_OPTIONAL,
            'Message to send / encrypt / decrypt. If missing will read the message from stdin', '');
        return $this;
    }

    protected function getPrivateKey(InputInterface $input, OutputInterface $output): string
    {
        if (!empty($input->getOption('private-key'))) {
            $output->writeln('<error>Private keys on the command line may be insecure. Use --key-file file instead.</error>',
                OutputInterface::VERBOSITY_VERBOSE);
        }
        return $this->getKey($input->getOption('private-key'), 'private', Constants::PRIVATE_KEY_PREFIX);
    }

    protected function getPublicKey(InputInterface $input): string
    {
        return $this->getKey($input->getArgument('public-key'), 'public', Constants::PUBLIC_KEY_PREFIX);
    }

    protected function getMessage(InputInterface $input, string $optionName = 'message'): string
    {
        $message = $input->getOption($optionName);
        if (empty($message)) {
            $message = $this->readStdInput();
        }
        return $message;
    }

    protected function loadDefaults(InputInterface $input, OutputInterface $output)
    {
        $keyFile = $input->getOption('key-file');
        if (!file_exists($keyFile)) {
            if ($keyFile != self::KEY_FILE) {
                throw new InvalidArgumentException('Could not find key file ' . $keyFile);
            }
            $output->writeln('<error>Cannot find ' . self::KEY_FILE . ' in the current working directory</error>',
                OutputInterface::VERBOSITY_VERBOSE);
            // there are no defaults to load: we can't find the key file
            return;
        }
        $result = parse_ini_file($keyFile, false, INI_SCANNER_TYPED);
        if ($result === false) {
            throw new InvalidArgumentException('Unable to parse ' . $keyFile . ' for default values');
        }
        $this->defaults = $result;
    }

    protected function getDefault(string $argumentName): string
    {
        return $this->defaults[$argumentName] ?? '';
    }

    private function readStdInput(): string
    {
        // read console standard input stream. Strip empty / blank lines
        return join("\n", array_filter(array_map('trim', file('php://stdin'))));
    }

    private function getKey(string $key, string $optionName, string $keyPrefix): string
    {
        if (empty($key)) {
            $key = $this->getDefault($optionName);
        } else if (file_exists($key)) {
            $key = file_get_contents($key);
        }
        if (empty($key)) {
            throw new InvalidArgumentException(ucfirst($optionName) . ' key invalid or missing');
        }
        return CryptTool::getInstance()->hex2bin(str_replace($keyPrefix, '', $key));
    }
}