<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\CapabilityResponse;
use Threema\MsgApi\Commands\Results\Response;

class Capability implements CommandInterface
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

    public function getPath(): string
    {
        return 'capabilities/' . $this->threemaId;
    }

    /**
     * @param int    $httpCode
     * @param string $response
     * @return CapabilityResponse
     */
    public function parseResult(int $httpCode, string $response): Response
    {
        return new CapabilityResponse($httpCode, $response);
    }
}
