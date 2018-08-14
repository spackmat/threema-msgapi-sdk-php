<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\LookupIdResponse;
use Threema\MsgApi\Commands\Results\Response;

class LookupPhone implements CommandInterface
{
    /**
     * @var string
     */
    private $phoneNumber;

    /** @var string */
    private $hashedPhone;

    public function __construct(string $phoneNumber, string $hashedPhone)
    {
        $this->phoneNumber = $phoneNumber;
        $this->hashedPhone = $hashedPhone;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @return string
     */
    public function getHashedPhone(): string
    {
        return $this->hashedPhone;
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
        return 'lookup/phone_hash/' . $this->hashedPhone;
    }

    /**
     * @param int    $httpCode
     * @param string $response
     * @return LookupIdResponse
     */
    public function parseResult(int $httpCode, string $response): Response
    {
        return new LookupIdResponse($httpCode, $response);
    }
}
