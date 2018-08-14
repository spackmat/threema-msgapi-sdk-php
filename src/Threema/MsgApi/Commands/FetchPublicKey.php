<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\FetchPublicKeyResponse;

class FetchPublicKey implements CommandInterface
{
    /**
     * @var string
     */
    private $threemaId;

    /**
     * @param string $threemaId
     */
    public function __construct(string $threemaId)
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
     * @return FetchPublicKeyResponse
     */
    public function parseResult(int $httpCode, string $response): \Threema\MsgApi\Commands\Results\Response
    {
        return new FetchPublicKeyResponse($httpCode, $response);
    }
}
