<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\FetchPublicKeyResult;

class FetchPublicKey implements CommandInterface
{
    /**
     * @var string
     */
    private $threemaId;

    /**
     * @param string $threemaId
     */
    public function __construct($threemaId)
    {
        $this->threemaId = $threemaId;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return 'pubkeys/' . $this->threemaId;
    }

    /**
     * @param int    $httpCode
     * @param string $response
     * @return FetchPublicKeyResult
     */
    public function parseResult(int $httpCode, string $response): \Threema\MsgApi\Commands\Results\Result
    {
        return new FetchPublicKeyResult($httpCode, $response);
    }
}
