<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\Console\Command;

use Threema\Console\Common;
use Threema\MsgApi\Connection;
use Threema\MsgApi\ConnectionSettings;
use Threema\MsgApi\Helpers\E2EHelper;

class SendE2EText extends Base
{
    public function __construct()
    {
        parent::__construct('Send End-to-End Encrypted Text Message',
            [self::argThreemaId, self::argPublicKey, self::argFrom, self::argSecret, self::argPrivateKey],
            'Encrypt standard input and send the text message to the given ID. \'from\' is the API identity and \'secret\' is the API secret. Prints the message ID on success.');
    }

    protected function doRun()
    {
        $threemaId         = $this->getArgumentThreemaId(self::argThreemaId);
        $receiverPublicKey = $this->getArgumentPublicKey(self::argPublicKey);
        $from              = $this->getArgument(self::argFrom);
        $secret            = $this->getArgument(self::argSecret);
        $privateKey        = $this->getArgumentPrivateKey(self::argPrivateKey);

        Common::required($threemaId, $receiverPublicKey, $from, $secret, $privateKey);

        $message = $this->readStdIn();
        if (strlen($message) === 0) {
            throw new \InvalidArgumentException('please define a message');
        }

        $settings  = new ConnectionSettings($from, $secret);
        $connector = new Connection($settings);

        $helper = new E2EHelper($privateKey, $connector);
        $result = $helper->sendTextMessage($threemaId, $receiverPublicKey, $message);

        if ($result->isSuccess()) {
            Common::l('Message ID: ' . $result->getMessageId());
        } else {
            Common::e('Error: ' . $result->getErrorMessage());
        }
    }
}
