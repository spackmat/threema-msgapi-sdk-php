<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Threema\MsgApi\Connection;
use Threema\MsgApi\Exceptions\Exception;
use Threema\MsgApi\Exceptions\InvalidArgumentException;
use Threema\MsgApi\Helpers\Constants;
use Threema\MsgApi\Response\Response;

abstract class AbstractNetworkedCommand extends AbstractLocalCommand
{
    protected function configure()
    {
        parent::configure();
        $this->addOption('from', 'f', InputOption::VALUE_OPTIONAL,
            'Sender Threema ID: the API identity. Usually starts with *');
        $this->addOption('secret', 's', InputOption::VALUE_OPTIONAL,
            'Sender Secret: the API secret from https://gateway.threema.ch');
    }

    protected function getConnection(InputInterface $input, OutputInterface $output): Connection
    {
        $this->loadDefaults($input, $output);
        return $this->connectionFactory->getConnection($this->getSenderID($input), $this->getSecret($input, $output));
    }

    protected function assertSuccess(Response $result)
    {
        if ($result->isSuccess()) {
            return;
        }
        throw new Exception($result->getErrorMessage());
    }

    protected function requireRecipientID()
    {
        $this->addArgument('threema-id', InputArgument::REQUIRED,
            'Recipient Threema ID (to send a message to or query)');
        return $this;
    }

    protected function getRecipientID(InputInterface $input, string $argumentName = 'threema-id'): string
    {
        return $this->validThreemaID($input->getArgument($argumentName));
    }

    protected function getSenderID(InputInterface $input, string $optionName = 'from'): string
    {
        return $this->validThreemaID($input->getOption($optionName) ?: $this->getDefault($optionName) ?: $this->getDefault('id'));
    }

    protected function validThreemaID(string $id): string
    {
        if (strlen($id) !== Constants::THREEMA_ID_LENGTH) {
            throw new InvalidArgumentException('Threema ID invalid or missing');
        }
        return strtoupper($id);
    }

    protected function getSecret(InputInterface $input, OutputInterface $output,
        string $argumentName = 'secret'): string
    {
        $secret = $input->getOption($argumentName);
        if (empty($secret)) {
            $secret = $this->getDefault($argumentName);
        } else {
            $output->writeln('<error>Secrets on the command line may be insecure. Use --key-file file instead.</error>');
        }
        if (empty($secret)) {
            throw new InvalidArgumentException('Secret invalid or missing');
        }
        return $secret;
    }
}