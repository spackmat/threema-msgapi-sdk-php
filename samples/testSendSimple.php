<?php

use Threema\MsgApi\Connection;
use Threema\MsgApi\Receiver;

require_once '../vendor/autoload.php';

//define your connection settings
$driver = new \Threema\MsgApi\HttpDriver\CurlHttpDriver(
    '*YOUR_GATEWAY_THREEMA_ID',
    'YOUR_GATEWAY_THREEMA_ID_SECRET'
);

//create a connection
$connector = new Connection($driver);

//create a receiver
$receiver = new Receiver('ECHOECHO', Receiver::TYPE_ID);

$result = $connector->sendSimple($receiver, "This is a Test Message");
if ($result->isSuccess()) {
    echo 'Message ID: ' . $result->getMessageId() . "\n";
} else {
    echo 'Error: ' . $result->getErrorMessage() . "\n";
}
