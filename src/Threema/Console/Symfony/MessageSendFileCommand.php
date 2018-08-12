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
use Threema\MsgApi\Helpers\E2EHelper;

class MessageSendFileCommand extends AbstractNetworkedCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('message:send:file')
             ->setDescription('Send a End-to-End Encrypted File Message')
             ->setHelp('Encrypt the file (and thumbnail if given) and send the message to the given ID. Prints the message ID on success')
             ->setAliases(['file'])
             ->requireRecipientID()
             ->requirePublicKey()
             ->addArgument('file', InputArgument::REQUIRED, 'Path and file name to encrypt and send')
             ->addArgument('thumbnail', InputArgument::OPTIONAL,
                 'Path to optional thumbnail image to display in the message');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getConnection($input, $output);
        $helper     = new E2EHelper($this->getPrivateKey($input, $output), $connection);
        $result     = $helper->sendFileMessage($this->getRecipientID($input), $this->getPublicKey($input),
            $input->getArgument('file'), $input->getArgument('thumbnail'));
        $this->assertSuccess($result);
        $output->writeln($result->getMessageId());
        return 0;
    }
}