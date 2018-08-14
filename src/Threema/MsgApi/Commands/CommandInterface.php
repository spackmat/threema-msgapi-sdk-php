<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\Response;

interface CommandInterface
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
