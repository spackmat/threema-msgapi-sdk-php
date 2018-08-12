<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\Console\Symfony;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Threema\MsgApi\Constants;

class VersionCommand extends AbstractLocalCommand
{
    protected function configure()
    {
        $this->setName('api:version')
             ->setAliases(['version'])
             ->setDescription('Show API version, Encryptor version, Feature level');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $left      = function (int $level) {
            return $level == Constants::MSGAPI_SDK_FEATURE_LEVEL ? '===>' : '';
        };
        $encryptor = $this->getEncryptor();
        $output->writeln('Threema PHP MsgApi Tool');
        $output->writeln('Gateway API Version: ' . Constants::MSGAPI_SDK_VERSION);
        $output->writeln('Encryptor: ' . $encryptor->getName() . ' (' . $encryptor->getDescription() . ')');
        $output->writeln('Feature level: ' . Constants::MSGAPI_SDK_FEATURE_LEVEL);
        $table = new Table($output);
        $table->getStyle()->setPadType(STR_PAD_LEFT);
        $table->setRows([
            ['', 'Level', 'Text', 'Capabilities', 'Image', 'File', 'Credits'],
            new TableSeparator(),
            [$left(1), '1', 'Y'],
            [$left(2), '2', 'Y', 'Y', 'Y', 'Y', new TableCell()],
            [$left(3), '3', 'Y', 'Y', 'Y', 'Y', 'Y']]);
        $table->render();
    }
}