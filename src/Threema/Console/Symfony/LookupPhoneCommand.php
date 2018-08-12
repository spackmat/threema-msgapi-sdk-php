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

class LookupPhoneCommand extends AbstractNetworkedCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('id:from-phone')
             ->setAliases(['phone'])
             ->setDescription('Lookup the Threema ID linked to the given phone number (will be hashed locally).')
             ->addArgument('phone', InputArgument::REQUIRED, 'phone number to hash and find');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->getConnection($input, $output)->keyLookupByPhoneNumber($input->getArgument('phone'));
        $this->assertSuccess($result);
        $output->writeln($result->getId());
        return 0;
    }
}