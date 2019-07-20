<?php
require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

use \RouterOS\Rosario;

// Initiate client with config object
$client = new Rosario([
    'timeout' => 1,
    'host'    => '127.0.0.1',
    'user'    => 'admin',
    'pass'    => 'admin'
]);

// Send query to RouterOS
$response = $client->write('/ip/firewall/address-list/print')->read();
print_r($response);
