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

$e2eHelper = new \Threema\MsgApi\Helpers\E2EHelper($senderPrivateKey, $connector);
$result    = $e2eHelper->sendTextMessage("TEST1234", "thePublicKeyAsHex", "This is an end-to-end encrypted message");

if (true === $result->isSuccess()) {
    echo 'Message ID: ' . $result->getMessageId() . "\n";
} else {
    echo 'Error: ' . $result->getErrorMessage() . "\n";
}
