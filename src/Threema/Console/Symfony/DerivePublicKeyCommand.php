<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DerivePublicKeyCommand extends AbstractLocalCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('key:derive-public')
             ->setAliases(['dpk'])
             ->setDescription('Derive the public key that corresponds with the given private key.')
             ->requirePrivateKey();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadDefaults($input, $output);
        $encryptor = $this->getEncryptor();
        $publicKey = $encryptor->derivePublicKey($encryptor->hex2bin($this->getPrivateKey($input, $output)));
        $output->writeln($encryptor->bin2hex($publicKey));
        return 0;
    }
}