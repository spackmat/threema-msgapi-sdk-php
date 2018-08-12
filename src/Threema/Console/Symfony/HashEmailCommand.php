<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Threema\MsgApi\Encryptor\AbstractEncryptor;

class HashEmailCommand extends AbstractLocalCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('hash:email')
             ->setAliases(['he'])
             ->setDescription('Hash an email address for identity lookup. Prints the hash in hex.')
             ->addArgument('email', InputArgument::REQUIRED, 'The email address to hash');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $encryptor = AbstractEncryptor::getInstance();
        $output->writeln($encryptor->hashEmail($input->getArgument('email')));
        return 0;
    }
}