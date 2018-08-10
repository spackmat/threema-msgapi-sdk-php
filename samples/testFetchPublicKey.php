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


$result = $connector->fetchPublicKey('ECHOECHO');
if($result->isSuccess()) {
	echo 'public key '.$result->getPublicKey() . "\n";
}
else {
	echo 'error '.$result->getErrorMessage() . "\n";
}
