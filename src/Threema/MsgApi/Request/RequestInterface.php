<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Request;

use Threema\MsgApi\Response\Response;

interface RequestInterface
{
    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return array
     */
    public function getParams(): array;

    /**
     * @param int    $httpCode
     * @param string $response
     * @return Response
     */
    public function parseResult(int $httpCode, string $response): Response;
}
