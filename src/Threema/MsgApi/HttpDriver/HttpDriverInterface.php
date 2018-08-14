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
use Threema\MsgApi\Response\Response;

/**
 */
interface HttpDriverInterface
{
    /**
     * @param CommandInterface $command
     * @param \Closure         $progress
     * @return Response
     */
    public function get(CommandInterface $command, \Closure $progress = null): Response;

    /**
     * @param CommandInterface $command
     * @return Response
     */
    public function postForm(CommandInterface $command): Response;

    /**
     * @param MultiPartCommandInterface $command
     * @return Response
     */
    public function postMultiPart(MultiPartCommandInterface $command): Response;

    /**
     * @param JsonCommandInterface $command
     * @return Response
     */
    public function postJson(JsonCommandInterface $command): Response;
}