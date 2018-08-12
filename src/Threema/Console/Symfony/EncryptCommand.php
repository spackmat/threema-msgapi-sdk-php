<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Threema\MsgApi\Encryptor\AbstractEncryptor;

class EncryptCommand extends AbstractLocalCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('encrypt')
             ->setDescription('Encrypt standard input using the given sender private key and recipient public key. ' .
                 'Two lines to standard output: first the nonce (hex), and then the box (hex) containing the encrypted message.')
             ->requirePrivateKey()
             ->requirePublicKey()
             ->optionalMessageOrStdIn();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadDefaults($input, $output);
        $encryptor = AbstractEncryptor::getInstance();
        $nonce     = $encryptor->randomNonce();
        $text      = $encryptor->encryptMessageText($this->getMessage($input), $this->getPrivateKey($input, $output),
            $this->getPublicKey($input), $nonce);

        $output->writeln('Nonce:', OutputInterface::VERBOSITY_VERBOSE);
        $output->writeln($encryptor->bin2hex($nonce));

        $output->writeln('Encrypted Text:', OutputInterface::VERBOSITY_VERBOSE);
        $output->writeln($encryptor->bin2hex($text));
        return 0;
    }
}