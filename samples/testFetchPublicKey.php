<?php
declare(strict_types=1);

require_once '../vendor/autoload.php';

$factory    = new \Threema\MsgApi\ConnectionFactory();
$connection = $factory->getConnection('*YOUR_GATEWAY_THREEMA_ID', 'YOUR_GATEWAY_THREEMA_ID_SECRET');

$result = $connector->fetchPublicKey('ECHOECHO');
if ($result->isSuccess()) {
    echo 'public key ' . $result->getPublicKey() . "\n";
} else {
    echo 'error ' . $result->getErrorMessage() . "\n";
}
