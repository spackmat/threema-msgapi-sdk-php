<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Commands\Results;

use Threema\MsgApi\Commands\LookupBulk;
use Threema\MsgApi\Helpers\BulkLookupIdentity;

class LookupBulkResult extends Result
{
    /** @var \Threema\MsgApi\Commands\LookupBulk */
    private $request;

    /** @var BulkLookupIdentity[] */
    private $matches = [];

    public function __construct(int $httpCode, $response, LookupBulk $request)
    {
        $this->request = $request;
        parent::__construct($httpCode, $response);
    }

    /**
     * @return BulkLookupIdentity[]
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * @param string $response json
     */
    protected function processResponse($response)
    {
        $matches = json_decode($response, true);
        if (empty($matches)) {
            return;
        }
        foreach ($matches as $match) {
            $identity                 = $match['identity'];
            $this->matches[$identity] = new BulkLookupIdentity(
                $identity,
                $match['publicKey'],
                $this->request->findEmail($match['emailHash'] ?? ''),
                $this->request->findPhone($match['phoneHash'] ?? ''));
        }
    }

    /**
     * @param int $httpCode
     * @return string
     */
    protected function getErrorMessageByErrorCode($httpCode)
    {
        switch ($httpCode) {
            case 400:
                return 'JSON is invalid or hash length is wrong';
            case 401:
                return 'API identity or secret incorrect';
            case 404:
                return 'No matching ID found';
            case 413:
                return 'Too many hashes in the request'; // email + phone <= 1000
            case 500:
                return 'A temporary internal server error has occurred';
            default:
                return 'Unknown error';
        }
    }
}
