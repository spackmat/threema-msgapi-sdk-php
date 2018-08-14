<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\Response;
use Threema\MsgApi\Commands\Results\SendE2EResponse;

class SendE2E implements CommandInterface
{
    /**
     * @var string
     */
    private $nonce;

    /**
     * @var string
     */
    private $box;

    /**
     * @var string
     */
    private $threemaId;

    /**
     * @param string $threemaId
     * @param string $nonce hex
     * @param string $box   hex
     */
    public function __construct(string $threemaId, string $nonce, string $box)
    {
        $this->nonce     = $nonce;
        $this->box       = $box;
        $this->threemaId = $threemaId;
    }

    /**
     * @return string
     */
    public function getThreemaId(): string
    {
        return $this->threemaId;
    }

    /**
     * @return string
     */
    public function getNonce(): string
    {
        return $this->nonce;
    }

    /**
     * @return string
     */
    public function getBox(): string
    {
        return $this->box;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $p['to']    = $this->threemaId;
        $p['nonce'] = $this->nonce;
        $p['box']   = $this->box;
        return $p;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return 'send_e2e';
    }

    /**
     * @param int    $httpCode
     * @param string $response
     * @return SendE2EResponse
     */
    public function parseResult(int $httpCode, string $response): Response
    {
        return new SendE2EResponse($httpCode, $response);
    }
}
