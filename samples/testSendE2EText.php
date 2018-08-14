<?php
declare(strict_types=1);

require_once '../vendor/autoload.php';

$factory    = new \Threema\MsgApi\ConnectionFactory();
$connection = $factory->getConnection('*YOUR_GATEWAY_THREEMA_ID', 'YOUR_GATEWAY_THREEMA_ID_SECRET');

$result = $connection->sendTextMessage("MY_PRIVATE_KEY_AS_HEX", "TEST1234", "thePublicKeyAsHex",
    "This is an end-to-end encrypted message");

if (true === $result->isSuccess()) {
    echo 'Message ID: ' . $result->getMessageId() . "\n";
} else {
    echo 'Error: ' . $result->getErrorMessage() . "\n";
}
