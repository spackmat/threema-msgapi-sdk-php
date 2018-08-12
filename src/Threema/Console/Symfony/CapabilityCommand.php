<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CapabilityCommand extends AbstractNetworkedCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('id:capability')
             ->setAliases(['what'])
             ->setDescription('List the capabilities of a Threema ID')
             ->requireRecipientID();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->getConnection($input, $output)->keyCapability($this->getRecipientID($input));
        $this->assertSuccess($result);
        $output->writeln(implode(PHP_EOL, $result->getCapabilities()));
        return 0;
    }
}