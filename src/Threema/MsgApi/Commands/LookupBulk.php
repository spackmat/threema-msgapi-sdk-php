<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\LookupBulkResult;
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
    public function getParams()
    {
        return [];
    }

    public function getJson(): string
    {
        $encryptor = AbstractEncryptor::getInstance();
        $request   = [];
        foreach ($this->phone as $id => $phoneNumber) {
            $hashedPhoneNumber                          = $encryptor->hashPhoneNo($phoneNumber);
            $request['phoneHashes'][]                   = $hashedPhoneNumber;
            $this->hashedPhone[$hashedPhoneNumber][$id] = $phoneNumber;
        }
        foreach ($this->email as $id => $emailAddress) {
            $hashedEmail                          = $encryptor->hashEmail($emailAddress);
            $request['emailHashes'][]             = $hashedEmail;
            $this->hashedEmail[$hashedEmail][$id] = $emailAddress;
        }
        return json_encode($request);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return 'lookup/bulk';
    }

    /**
     * @param int    $httpCode
     * @param object $res
     * @return LookupBulkResult
     */
    public function parseResult($httpCode, $res)
    {
        return new LookupBulkResult($httpCode, $res, $this);
    }
}
