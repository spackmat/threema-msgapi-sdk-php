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
use Threema\MsgApi\Tools\CryptTool;

class ReceiveMessage extends Base
{
    const argOutputFolder = 'outputFolder';
    const argMessageId    = 'messageId';

    public function __construct()
    {
        parent::__construct('Decrypt a Message and download the Files',
            [self::argThreemaId,
             self::argPublicKey,
             self::argFrom,
             self::argSecret,
             self::argPrivateKey,
             self::argMessageId,
             self::argNonce],
            'Decrypt a box (must be provided on stdin) message and download (if the message is an image or file message) the file(s) to the given <' . self::argOutputFolder . '> folder',
            [self::argOutputFolder]);
    }

    protected function doRun()
    {
        $cryptTool = CryptTool::getInstance();

        $sendersThreemaId = $this->getArgumentThreemaId(self::argThreemaId);
        $sendersPublicKey = $this->getArgumentPublicKey(self::argPublicKey);
        $id               = $this->getArgumentThreemaId(self::argFrom);
        $secret           = $this->getArgument(self::argSecret);
        $privateKey       = $this->getArgumentPrivateKey(self::argPrivateKey);
        $nonce            = $cryptTool->hex2bin($this->getArgument(self::argNonce));
        $messageId        = $this->getArgument(self::argMessageId);
        $outputFolder     = $this->getArgument(self::argOutputFolder);

        $box = $cryptTool->hex2bin($this->readStdIn());

        Common::required($box, $id, $secret, $privateKey, $nonce);

        $settings = new ConnectionSettings(
            $id,
            $secret
        );

        $connector = new Connection($settings);
        $helper    = new E2EHelper($privateKey, $connector);
        $message   = $helper->receiveMessage(
            $sendersThreemaId,
            $sendersPublicKey,
            $messageId,
            $box,
            $nonce,
            $outputFolder
        );

        if (null === $message) {
            Common::e('invalid message');
            return;
        }

        if ($message->isSuccess()) {
            Common::l($message->getMessageId() . ' - ' . $message->getThreemaMessage());
            foreach ($message->getFiles() as $fileName => $filePath) {
                Common::l('   received file ' . $fileName . ' in ' . $filePath);
            }
        } else {
            Common::e('Error: ' . implode("\n", $message->getErrors()));
        }
    }
}
