<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands\Results;

abstract class Response
{
    /**
     * @var int
     */
    private $httpCode;

    /**
     * @var string
     */
    private $response;

    /**
     * @param int    $httpCode
     * @param string $response
     */
    public function __construct(int $httpCode, $response)
    {
        $this->httpCode = $httpCode;
        $this->processResponse($response);
        $this->response = $response;
    }

    final public function isSuccess()
    {
        return $this->httpCode == 200;
    }

    /**
     * @return int
     */
    final public function getErrorCode(): int
    {
        return $this->httpCode;
    }

    /**
     * @return string
     */
    final public function getErrorMessage(): string
    {
        return $this->getErrorMessageByErrorCode($this->getErrorCode());
    }

    /**
     * @return string
     */
    final public function getRawResponse()
    {
        return $this->response;
    }

    /**
     * @param int $httpCode
     * @return string
     */
    abstract protected function getErrorMessageByErrorCode(int $httpCode): string;

    /**
     * @param string $response
     */
    abstract protected function processResponse(string $response);
}
