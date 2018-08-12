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
use Threema\MsgApi\Exceptions\BadMessageException;

class MessageReceive extends AbstractNetworkedCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('message:receive')
             ->setDescription('Decrypt a message and optionally download any attached files to a specified folder')
             ->addArgument('sender-key', InputArgument::REQUIRED, 'Public Key of the sender')
             ->addArgument('message-id', InputArgument::REQUIRED, 'Unique ID of the message')
             ->addArgument('nonce', InputArgument::REQUIRED, 'Nonce for the message (hex)')
             ->optionalMessageOrStdIn()
             ->addArgument('folder', InputArgument::OPTIONAL,
                 'Folder to store file attachments. If missing, attachments will be ignored.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getConnection($input, $output);
        $result     = $connection->receiveMessage(
            $this->getPrivateKey($input, $output),
            $input->getArgument('sender-key'),
            $input->getArgument('message-id'),
            $this->getMessage($input),
            $input->getArgument('nonce'),
            $input->getArgument('folder')
        );

        if (!$result->isSuccess()) {
            throw new BadMessageException(join(PHP_EOL, $result->getErrors()));
        }
        $output->writeln($result->getThreemaMessage()->__toString());
        foreach ($result->getFiles() as $fileName => $filePath) {
            $output->writeln('   file: ' . $filePath . ' ' . $fileName);
        }
        return 0;
    }
}