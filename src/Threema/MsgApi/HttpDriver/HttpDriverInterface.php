<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\HttpDriver;

use Threema\MsgApi\Request\RequestInterface;
use Threema\MsgApi\Request\JsonRequestInterface;
use Threema\MsgApi\Request\MultiPartRequestInterface;
use Threema\MsgApi\Response\Response;

/**
 */
interface HttpDriverInterface
{
    /**
     * @param RequestInterface $command
     * @param \Closure         $progress
     * @return Response
     */
    public function get(RequestInterface $command, \Closure $progress = null): Response;

    /**
     * @param RequestInterface $command
     * @return Response
     */
    public function postForm(RequestInterface $command): Response;

    /**
     * @param MultiPartRequestInterface $command
     * @return Response
     */
    public function postMultiPart(MultiPartRequestInterface $command): Response;

    /**
     * @param JsonRequestInterface $command
     * @return Response
     */
    public function postJson(JsonRequestInterface $command): Response;
}