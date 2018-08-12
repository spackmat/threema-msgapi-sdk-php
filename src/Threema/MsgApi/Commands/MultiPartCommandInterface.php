<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands;

interface MultiPartCommandInterface extends CommandInterface
{
    public function getData(): string;
}
