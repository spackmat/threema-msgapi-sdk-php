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

class SendE2EImage extends Base
{
    const argImageFile = 'imageFile';

    public function __construct()
    {
        parent::__construct('Send a End-to-End Encrypted Image Message',
            [self::argThreemaId,
             self::argPublicKey,
             self::argFrom,
             self::argSecret,
             self::argPrivateKey,
             self::argImageFile],
            'Encrypt the image file and send the message to the given ID. \'from\' is the API identity and \'secret\' is the API secret. Prints the message ID on success.');
    }

    protected function doRun()
    {
        $threemaId         = $this->getArgumentThreemaId(self::argThreemaId);
        $receiverPublicKey = $this->getArgumentPublicKey(self::argPublicKey);
        $from              = $this->getArgument(self::argFrom);
        $secret            = $this->getArgument(self::argSecret);
        $privateKey        = $this->getArgumentPrivateKey(self::argPrivateKey);

        $path = $this->getArgumentFile(self::argImageFile);

        Common::required($threemaId, $from, $secret, $privateKey, $path);

        $settings = new ConnectionSettings(
            $from,
            $secret
        );

        $connector = new Connection($settings);

        $helper = new E2EHelper($privateKey, $connector);
        $result = $helper->sendImageMessage($threemaId, $receiverPublicKey, $path);

        if ($result->isSuccess()) {
            Common::l('Message ID: ' . $result->getMessageId());
        } else {
            Common::e('Error: ' . $result->getErrorMessage());
        }
    }
}
