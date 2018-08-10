<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\Console\Command;

use Threema\Console\Common;
use Threema\MsgApi\Connection;
use Threema\MsgApi\ConnectionSettings;

class LookupIdByPhoneNo extends Base
{
    const argPhoneNo = 'phoneNo';

    public function __construct()
    {
        parent::__construct('ID-Lookup By Phone Number',
            [self::argPhoneNo, self::argFrom, self::argSecret],
            'Lookup the ID linked to the given phone number (will be hashed locally).');
    }

    protected function doRun()
    {
        $phoneNo = $this->getArgument(self::argPhoneNo);
        $from    = $this->getArgumentThreemaId(self::argFrom);
        $secret  = $this->getArgument(self::argSecret);

        Common::required($phoneNo, $from, $secret);

        //define connection settings
        $settings = new ConnectionSettings($from, $secret);

        //create a connection
        $connector = new Connection($settings);

        $result = $connector->keyLookupByPhoneNumber($phoneNo);;
        Common::required($result);
        if ($result->isSuccess()) {
            Common::l($result->getId());
        } else {
            Common::e($result->getErrorMessage());
        }
    }
}
