<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands\Results;

class LookupIdResponse extends Response
{
    /**
     * @var string
     */
    private $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $response
     */
    protected function processResponse(string $response)
    {
        $this->id = $response;
    }

    /**
     * @param int $httpCode
     * @return string
     */
    protected function getErrorMessageByErrorCode(int $httpCode): string
    {
        switch ($httpCode) {
            case 400:
                return 'Hash length is wrong';
            case 401:
                return 'API identity or secret incorrect';
            case 404:
                return 'No matching ID found';
            case 500:
                return 'A temporary internal server error has occurred';
            default:
                return 'Unknown error';
        }
    }

}
