<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Threema\MsgApi\Helpers\E2EHelper;

class MessageSendTextCommand extends AbstractNetworkedCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('message:send:text')
             ->setDescription('Send a End-to-End Encrypted Text Message')
             ->setHelp('Encrypt the text and send to the given ID. Prints the message ID on success')
             ->setAliases(['text'])
             ->requireRecipientID()
             ->requirePublicKey()
             ->requirePrivateKey()
             ->optionalMessageOrStdIn();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getConnection($input, $output);
        $helper     = new E2EHelper($this->getPrivateKey($input, $output), $connection);
        $result     = $helper->sendTextMessage($this->getRecipientID($input), $this->getPublicKey($input),
            $this->getMessage($input));
        $this->assertSuccess($result);
        $output->writeln($result->getMessageId());
        return 0;
    }
}