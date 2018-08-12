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

class LookupEmailCommand extends AbstractNetworkedCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('id:from-email')
             ->setAliases(['email'])
             ->setDescription('Lookup the Threema ID linked to the given email address (will be hashed locally).')
             ->addArgument('email', InputArgument::REQUIRED, 'Email address to hash and find');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->getConnection($input, $output)->keyLookupByEmail($input->getArgument('email'));
        $this->assertSuccess($result);
        $output->writeln($result->getId());
        return 0;
    }
}