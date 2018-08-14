<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands\Results;

class SendSimpleResult extends Result
{
    /**
     * @var string
     */
    private $messageId;

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @param string $response
     */
    protected function processResponse(string $response)
    {
        $this->messageId = $response;
    }

    /**
     * @param int $httpCode
     * @return string
     */
    protected function getErrorMessageByErrorCode(int $httpCode): string
    {
        switch ($httpCode) {
            case 400:
                return 'The recipient identity is invalid or the account is not set up for simple mode';
            case 401:
                return 'API identity or secret incorrect';
            case 402:
                return 'No credits remain';
            case 404:
                return 'Phone or email could not be found';
            case 413:
                return 'Message is too long';
            case 500:
                return 'A temporary internal server error has occurred';
            default:
                return 'Unknown error';
        }
    }
}
