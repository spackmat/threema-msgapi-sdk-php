<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands\Results;

class UploadFileResponse extends Response
{
    /**
     * @var string
     */
    private $blobId;

    /**
     * the generated blob id
     *
     * @return string
     */
    public function getBlobId(): string
    {
        return $this->blobId;
    }

    /**
     * @param string $response
     */
    protected function processResponse(string $response)
    {
        $this->blobId = $response;
    }

    /**
     * @param int $httpCode
     * @return string
     */
    protected function getErrorMessageByErrorCode(int $httpCode): string
    {
        switch ($httpCode) {
            case 401:
                return 'API identity or secret incorrect or file is empty';
            case 402:
                return 'No credits remain';
            case 413:
                return 'File is too long';
            case 500:
                return 'A temporary internal server error has occurred';
            default:
                return 'Unknown error';
        }
    }
}
