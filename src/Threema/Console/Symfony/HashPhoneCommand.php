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

class HashPhoneCommand extends AbstractLocalCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('hash:phone')
             ->setAliases(['hp'])
             ->setDescription('Hash a phone number for identity lookup. Only does the digits: strips other characters. Prints the hash in hex.')
             ->addArgument('phone', InputArgument::REQUIRED, 'The phone number to hash');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $encryptor = AbstractEncryptor::getInstance();
        $output->writeln($encryptor->hashPhoneNo($input->getArgument('phone')));
        return 0;
    }
}