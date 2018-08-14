<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreditsCommand extends AbstractNetworkedCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('api:credits')
             ->setAliases(['credits'])
             ->setDescription('Get the remaining message credits in your account');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->getConnection($input, $output)->credits();
        $this->assertSuccess($result);
        $output->writeln($result->getCredits() . ' remaining');
        return 0;
    }
}