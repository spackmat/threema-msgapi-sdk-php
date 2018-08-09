<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\Console\Command;

use Threema\Console\Common;
use Threema\MsgApi\Connection;
use Threema\MsgApi\ConnectionSettings;
use Threema\MsgApi\PublicKeyStore;

class LookupBulk extends Base
{
    const argEmail = 'emailOrPhone';

    /**
     * @var PublicKeyStore
     */
    private $publicKeyStore;

    /**
     * @param PublicKeyStore $publicKeyStore
     */
    public function __construct(PublicKeyStore $publicKeyStore)
    {
        parent::__construct('Bulk ID lookup',
            [self::argEmail, self::argFrom, self::argSecret],
            'Lookup the id and public key for the given list of comma separated phone numbers or emails.');
        $this->publicKeyStore = $publicKeyStore;
    }

    protected function doRun()
    {
        $wanted = $this->getArgument(self::argEmail);
        $from   = $this->getArgumentThreemaId(self::argFrom);
        $secret = $this->getArgument(self::argSecret);

        Common::required($wanted, $from, $secret);

        //define connection settings
        $settings = new ConnectionSettings($from, $secret);

        //create a connection
        $connector = new Connection($settings, $this->publicKeyStore);

        $emailAddresses = $phoneNumbers = [];
        foreach (explode(',', $wanted) as $item) {
            $item = trim($item);
            if (strpos($item, '@')) {
                $emailAddresses[] = $item;
            } else {
                $phoneNumbers[] = $item;
            }
        }
        $result = $connector->bulkLookup($emailAddresses, $phoneNumbers);
        if (!$result->isSuccess()) {
            Common::e($result->getErrorMessage());
            return;
        }
        $indent = 4;
        foreach ($result->getMatches() as $match) {
            $this->publicKeyStore->setPublicKey($match->getIdentity(), $match->getPublicKey());
            Common::l($match->getIdentity());
            Common::l(Common::convertPublicKey($match->getPublicKey()), $indent);
            foreach ($match->getEmails() as $email) {
                Common::l('email:' . $email, $indent);
            }
            foreach ($match->getPhones() as $phone) {
                Common::l('phone:' . $phone, $indent);
            }
        }
    }
}
