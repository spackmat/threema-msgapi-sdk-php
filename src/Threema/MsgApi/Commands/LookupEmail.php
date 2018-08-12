<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\LookupIdResult;
use Threema\MsgApi\Encryptor\AbstractEncryptor;

class LookupEmail implements CommandInterface
{
    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @param string $emailAddress
     */
    public function __construct($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return 'lookup/email_hash/' . urlencode(AbstractEncryptor::getInstance()->hashEmail($this->emailAddress));
    }

    /**
     * @param int    $httpCode
     * @param object $res
     * @return LookupIdResult
     */
    public function parseResult($httpCode, $res)
    {
        return new LookupIdResult($httpCode, $res);
    }
}
