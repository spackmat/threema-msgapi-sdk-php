<?php
declare(strict_types=1);

require_once '../vendor/autoload.php';

$factory    = new \Threema\MsgApi\ConnectionFactory();
$connection = $factory->getConnection('*YOUR_GATEWAY_THREEMA_ID', 'YOUR_GATEWAY_THREEMA_ID_SECRET');

$senderPrivateKey = "MY_PRIVATE_KEY_AS_HEX";
$filePath         = "/path/to/my/file.pdf";
$result           = $connection->sendFileMessage($senderPrivateKey, "TEST1234", "thePublicKeyAsHex", $filePath);

if (true === $result->isSuccess()) {
    echo 'File Message ID: ' . $result->getMessageId() . "\n";
} else {
    echo 'Error: ' . $result->getErrorMessage() . "\n";
}
