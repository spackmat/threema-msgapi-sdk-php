<?php
declare(strict_types=1);

require_once '../vendor/autoload.php';

$factory    = new \Threema\MsgApi\ConnectionFactory();
$connection = $factory->getConnection('*YOUR_GATEWAY_THREEMA_ID', 'YOUR_GATEWAY_THREEMA_ID_SECRET');

$result = $connection->sendToThreemaID('ECHOECHO', "This is a Test Message");
if ($result->isSuccess()) {
    echo 'Message ID: ' . $result->getMessageId() . "\n";
} else {
    echo 'Error: ' . $result->getErrorMessage() . "\n";
}
