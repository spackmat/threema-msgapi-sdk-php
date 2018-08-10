<?php

use Threema\MsgApi\Connection;
use Threema\MsgApi\ConnectionSettings;

require_once '../vendor/autoload.php';

//define your connection settings
$settings = new ConnectionSettings(
    '*YOUR_GATEWAY_THREEMA_ID',
    'YOUR_GATEWAY_THREEMA_ID_SECRET'
);

//create a connection
$connector = new Connection($settings);

$result = $connector->keyLookupByPhoneNumber('123456789');
if ($result->isSuccess()) {
    echo 'Threema ID found: ' . $result->getId() . "\n";
} else {
    echo 'Error: ' . $result->getErrorMessage() . "\n";
}
