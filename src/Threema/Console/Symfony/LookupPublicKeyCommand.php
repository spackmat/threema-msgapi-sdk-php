<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LookupPublicKeyCommand extends AbstractNetworkedCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('id:public-key')
             ->setAliases(['key'])
             ->setDescription('Get the public key for the given Threema ID')
             ->requireRecipientID();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->getConnection($input, $output)->fetchPublicKey($this->getRecipientID($input));
        $this->assertSuccess($result);
        $output->writeln($result->getPublicKey());
        return 0;
    }
}