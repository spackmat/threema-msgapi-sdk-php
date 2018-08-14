<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands\Results;

class DownloadFileResponse extends Response
{
    /**
     * @var string
     */
    private $data;

    /**
     * the generated blob id
     *
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $response
     */
    protected function processResponse(string $response)
    {
        $this->data = $response;
    }

    /**
     * @param int $httpCode
     * @return string
     */
    protected function getErrorMessageByErrorCode(int $httpCode): string
    {
        switch ($httpCode) {
            case 401:
                return 'API identity or secret incorrect';
            case 404:
                return 'Invalid blob id';
            case 500:
                return 'A temporary internal server error has occurred';
            default:
                return 'Unknown error';
        }
    }
}

