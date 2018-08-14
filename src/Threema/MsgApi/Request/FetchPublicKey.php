<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Request;

use Threema\MsgApi\Response\FetchPublicKeyResponse;

class FetchPublicKey implements RequestInterface
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
    public function parseResult(int $httpCode, string $response): \Threema\MsgApi\Response\Response
    {
        return new FetchPublicKeyResponse($httpCode, $response);
    }
}
