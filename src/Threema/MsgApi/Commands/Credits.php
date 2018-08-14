<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\CreditsResult;

class Credits implements CommandInterface
{
    /**
     * @return array
     */
    public function getParams(): array
    {
        return [];
    }

    public function getPath(): string
    {
        return 'credits';
    }

    /**
     * @param int    $httpCode
     * @param string $response
     * @return CreditsResult
     */
    public function parseResult(int $httpCode, string $response): \Threema\MsgApi\Commands\Results\Result
    {
        return new CreditsResult($httpCode, $response);
    }
}
