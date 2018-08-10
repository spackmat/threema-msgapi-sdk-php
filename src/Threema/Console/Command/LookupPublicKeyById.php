<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\Console\Command;

use Threema\Console\Common;
use Threema\MsgApi\Connection;
use Threema\MsgApi\ConnectionSettings;

class LookupPublicKeyById extends Base
{
    public function __construct()
    {
        parent::__construct('Fetch Public Key',
            [self::argThreemaId, self::argFrom, self::argSecret],
            'Lookup the public key for the given ID.');
    }

    protected function doRun()
    {
        $id     = $this->getArgumentThreemaId(self::argThreemaId);
        $from   = $this->getArgumentThreemaId(self::argFrom);
        $secret = $this->getArgument(self::argSecret);

        Common::required($id, $from, $secret);

        //define connection settings
        $settings = new ConnectionSettings($from, $secret);

        //create a connection
        $connector = new Connection($settings);

        $result = $connector->fetchPublicKey($id);
        if ($result->isSuccess()) {
            Common::l(Common::convertPublicKey($result->getPublicKey()));
        } else {
            Common::e($result->getErrorMessage());
        }
    }
}
