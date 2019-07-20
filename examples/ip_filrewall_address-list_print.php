<?php
require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

use \RouterOS\Client;

// Initiate client with config object
$client = new Client([
    'timeout' => 1,
    'host'    => '127.0.0.1',
    'user'    => 'admin',
    'pass'    => 'admin'
]);

// Send query to RouterOS and parse response
$response = $client->write('/ip/firewall/address-list/print')->read();

// You could treat response as an array except using array_* function

// Export every row using foreach
foreach ($response as $row) {
    echo current($row) . PHP_EOL;
}

$item = current($response);
var_dump($item);
echo PHP_EOL;

$item = end($response);
var_dump($item);
echo PHP_EOL;

$item = current($response);
var_dump($item);
echo PHP_EOL;

$item = reset($response);
var_dump($item);
echo PHP_EOL;

$item = current($response);
var_dump($item);
echo PHP_EOL;
