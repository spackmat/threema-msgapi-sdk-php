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
use Threema\Console\Common;
use Threema\MsgApi\Tools\CryptTool;

class GenerateKeyPairCommand extends AbstractLocalCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('key:create-pair')
             ->setAliases(['kp'])
             ->setDescription('Generate a new key pair and write the private and public keys to the respective files (in hex).')
             ->addArgument('private-key-file', InputArgument::OPTIONAL,
                 'Name of the private key file eg private.key. If missing will be printed to stdout')
             ->addArgument('public-key-file', InputArgument::OPTIONAL,
                 'Name of the public key file eg public.key. If missing will be printed to stdout');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cryptTool = CryptTool::getInstance();
        $keyPair   = $cryptTool->generateKeyPair();
        $this->writeKey($input->getArgument('private-key-file'),
            Common::convertPrivateKey($cryptTool->bin2hex($keyPair->getPrivateKey())), $output);
        $this->writeKey($input->getArgument('public-key-file'),
            Common::convertPublicKey($cryptTool->bin2hex($keyPair->getPublicKey())), $output);
        $output->writeln('Key pair generated', OutputInterface::VERBOSITY_VERBOSE);
        return 0;
    }

    private function writeKey(?string $fileName, string $key, OutputInterface $output)
    {
        if (empty($fileName)) {
            $output->writeln($key);
        } else {
            file_put_contents($fileName, $key . "\n");
        }
    }
}