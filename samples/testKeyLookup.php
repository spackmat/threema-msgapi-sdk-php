<?php
declare(strict_types=1);

use Threema\MsgApi\Connection;

require_once '../vendor/autoload.php';

$factory    = new \Threema\MsgApi\ConnectionFactory();
$connection = $factory->getConnection('*YOUR_GATEWAY_THREEMA_ID', 'YOUR_GATEWAY_THREEMA_ID_SECRET');

$result = $connection->keyLookupByPhoneNumber('123456789');
if ($result->isSuccess()) {
    echo 'Threema ID found: ' . $result->getId() . "\n";
} else {
    echo 'Error: ' . $result->getErrorMessage() . "\n";
}
