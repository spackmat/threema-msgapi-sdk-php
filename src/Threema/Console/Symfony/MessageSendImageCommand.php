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

class MessageSendImageCommand extends AbstractNetworkedCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('message:send:image')
             ->setDescription('Send a End-to-End Encrypted Image Message')
             ->setHelp('Encrypt the image file and send the message to the given ID. Prints the message ID on success')
             ->setAliases(['image'])
             ->requireRecipientID()
             ->requirePublicKey()
             ->requirePrivateKey()
             ->addArgument('file', InputArgument::REQUIRED, 'Path and file name to encrypt and send');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getConnection($input, $output);
        $result     = $connection->sendImageMessage(
            $this->getPrivateKey($input, $output),
            $this->getRecipientID($input),
            $this->getPublicKey($input),
            $input->getArgument('file'));
        $this->assertSuccess($result);
        $output->writeln($result->getMessageId());
        return 0;
    }
}