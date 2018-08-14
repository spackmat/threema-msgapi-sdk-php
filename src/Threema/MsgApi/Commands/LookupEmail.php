<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\LookupIdResult;
use Threema\MsgApi\Commands\Results\Result;

class LookupEmail implements CommandInterface
{
    /**
     * @var string
     */
    private $emailAddress;

    /** @var string */
    private $hashedEmail;

    /**
     * @param string $emailAddress
     */
    public function __construct(string $emailAddress, string $hashedEmail)
    {
        $this->emailAddress = $emailAddress;
        $this->hashedEmail  = $hashedEmail;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @return string
     */
    public function getHashedEmail(): string
    {
        return $this->hashedEmail;
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
        return 'lookup/email_hash/' . $this->hashedEmail;
    }

    /**
     * @param int    $httpCode
     * @param string $response
     * @return LookupIdResult
     */
    public function parseResult(int $httpCode, string $response): Result
    {
        return new LookupIdResult($httpCode, $response);
    }
}
