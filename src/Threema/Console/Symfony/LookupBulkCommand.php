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
use Threema\MsgApi\Helpers\KeyPrefix;

class LookupBulkCommand extends AbstractNetworkedCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('id:bulk')
             ->setAliases(['bulk'])
             ->setDescription('Get the Threema ID and public key for the given list of space separated phone numbers or emails.')
             ->addArgument('phones-or-emails', InputArgument::IS_ARRAY,
                 'The email addresses or phone numbers to look up', []);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $emailAddresses = $phoneNumbers = [];
        foreach ($input->getArgument('phones-or-emails') as $item) {
            $item = trim($item);
            if (strpos($item, '@')) {
                $emailAddresses[] = $item;
            } else {
                $phoneNumbers[] = $item;
            }
        }
        $result = $this->getConnection($input, $output)->bulkLookup($emailAddresses, $phoneNumbers);
        $this->assertSuccess($result);
        $indent = '    ';
        foreach ($result->getMatches() as $match) {
            $output->writeln($match->getIdentity());
            $output->writeln($indent . KeyPrefix::addPublic($match->getPublicKey()));
            foreach ($match->getEmails() as $email) {
                $output->writeln($indent . 'email:' . $email);
            }
            foreach ($match->getPhones() as $phone) {
                $output->writeln($indent . 'phone:' . $phone);
            }
        }
        return 0;
    }
}