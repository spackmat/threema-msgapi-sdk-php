<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\LookupBulkResult;
use Threema\MsgApi\Commands\Results\Result;
use Threema\MsgApi\Encryptor\AbstractEncryptor;

/**
 * It is possible (though very unlikely) that two different phone numbers or emails will hash to the same value.
 * This is a run time error that depends on user provided data so we can't just throw an exception or assert: we need
 * to smoothly handle the issue.
 *
 * Threema never sees the original emails or phone numbers: just hashes of them. So when it returns the hashes that
 * match an ID and public key, we connect them back to the original email address and phone numbers in a
 * BulkLookupIdentity helper class. The original key of the email or phone is returned to you in the match result, which
 * is helpful if it is linked to a user.id or person.id for example.
 */
class LookupBulk implements JsonCommandInterface
{
    /** @var string[] */
    private $email = [];

    /** @var string[] should include international country code prefix */
    private $phone = [];

    /** @var string[][] phone hash => phone numbers that match the hash */
    private $hashedPhone = [];

    /** @var string[][] email hash => emails that match the hash */
    private $hashedEmail = [];

    public function __construct(array $emailAddresses, array $phoneNumbers)
    {
        $this->email = array_filter(array_unique($emailAddresses));
        $this->phone = array_filter(array_unique($phoneNumbers));
    }

    public function findEmail(string $emailHash): array
    {
        return $this->hashedEmail[$emailHash] ?? [];
    }

    public function findPhone(string $phoneHash): array
    {
        return $this->hashedPhone[$phoneHash] ?? [];
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [];
    }

    public function getJson(): string
    {
        return json_encode(['emailHashes' => array_keys($this->hashedEmail),
                            'phoneHashes' => array_keys($this->hashedPhone)]) ?: '';
    }

    public function calculateHashes(AbstractEncryptor $encryptor)
    {
        foreach ($this->phone as $id => $phoneNumber) {
            $this->hashedPhone[$encryptor->hashPhoneNo($phoneNumber)][$id] = $phoneNumber;
        }
        foreach ($this->email as $id => $emailAddress) {
            $this->hashedEmail[$encryptor->hashEmail($emailAddress)][$id] = $emailAddress;
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return 'lookup/bulk';
    }

    /**
     * @param int    $httpCode
     * @param string $response
     * @return LookupBulkResult
     */
    public function parseResult(int $httpCode, string $response): Result
    {
        return new LookupBulkResult($httpCode, $response, $this);
    }
}
