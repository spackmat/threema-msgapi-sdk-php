<?php

use Threema\MsgApi\Connection;

require_once '../vendor/autoload.php';

//define your connection settings
$driver = new \Threema\MsgApi\HttpDriver\CurlHttpDriver(
    '*YOUR_GATEWAY_THREEMA_ID',
    'YOUR_GATEWAY_THREEMA_ID_SECRET'
);

//create a connection
$connector = new Connection($driver);

$senderPrivateKey = "MY_PUBLIC_KEY_IN_BIN";
$filePath         = "/path/to/my/file.pdf";

$e2eHelper = new \Threema\MsgApi\Helpers\E2EHelper($senderPrivateKey, $connector);
$result    = $e2eHelper->sendFileMessage("TEST1234", "thePublicKeyAsHex", $filePath);

if (true === $result->isSuccess()) {
    echo 'File Message ID: ' . $result->getMessageId() . "\n";
} else {
    echo 'Error: ' . $result->getErrorMessage() . "\n";
}
