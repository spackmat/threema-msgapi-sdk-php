<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\CapabilityResult;

class Capability implements CommandInterface
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

    public function getPath(): string
    {
        return 'capabilities/' . $this->threemaId;
    }

    /**
     * @param int    $httpCode
     * @param object $res
     * @return CapabilityResult
     */
    public function parseResult($httpCode, $res): \Threema\MsgApi\Commands\Results\Result
    {
        return new CapabilityResult($httpCode, $res);
    }
}
