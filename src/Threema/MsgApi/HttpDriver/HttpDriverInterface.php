<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\HttpDriver;

use Threema\MsgApi\Commands\CommandInterface;
use Threema\MsgApi\Commands\JsonCommandInterface;
use Threema\MsgApi\Commands\MultiPartCommandInterface;
use Threema\MsgApi\Commands\Results\Result;

/**
 */
interface HttpDriverInterface
{
    /**
     * @param CommandInterface $command
     * @param \Closure         $progress
     * @return Result
     */
    public function get(CommandInterface $command, \Closure $progress = null): Result;

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function postForm(CommandInterface $command): Result;

    /**
     * @param MultiPartCommandInterface $command
     * @return Result
     */
    public function postMultiPart(MultiPartCommandInterface $command): Result;

    /**
     * @param JsonCommandInterface $command
     * @return Result
     */
    public function postJson(JsonCommandInterface $command): Result;
}