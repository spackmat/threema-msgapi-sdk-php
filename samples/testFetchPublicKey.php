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

$result = $connector->fetchPublicKey('ECHOECHO');
if ($result->isSuccess()) {
    echo 'public key ' . $result->getPublicKey() . "\n";
} else {
    echo 'error ' . $result->getErrorMessage() . "\n";
}
