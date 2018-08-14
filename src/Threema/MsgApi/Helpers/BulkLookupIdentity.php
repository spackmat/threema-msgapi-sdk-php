<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Helpers;

class BulkLookupIdentity
{
    /** @var string 8 character threema id */
    private $identity;

    /** @var string */
    private $publicKey;

    /** @var string[] emails that match */
    private $emails;

    /** @var string[] phones that match */
    private $phones;

    public function __construct(string $identity, string $publicKey, array $emails = [], array $phones = [])
    {
        $this->identity  = $identity;
        $this->publicKey = $publicKey;
        $this->emails    = $emails;
        $this->phones    = $phones;
    }

    /**
     * @return string
     */
    public function getIdentity(): string
    {
        return $this->identity;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return string[] id => email address
     */
    public function getEmails(): array
    {
        return $this->emails;
    }

    /**
     * @return string[] id => phone number
     */
    public function getPhones(): array
    {
        return $this->phones;
    }

    public function getFirstEmail(): string
    {
        return array_values($this->emails)[0] ?? '';
    }

    public function getFirstPhone(): string
    {
        return array_values($this->phones)[0] ?? '';
    }
}
