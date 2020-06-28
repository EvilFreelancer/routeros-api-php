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

for ($i = 0; $i < 10; $i++) {
    $response = $client->qr('/ip/address/print');
    print_r($response);

    $response = $client->qr('/ip/arp/print');
    print_r($response);

    $response = $client->qr('/interface/print');
    print_r($response);
}
