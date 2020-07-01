<?php
require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

use \RouterOS\Client;

// Initiate client with config object
$client = new Client([
    'host'    => '127.0.0.1',
    'user'    => 'admin',
    'pass'    => 'admin'
]);

$out = $client->query(['/queue/simple/add', '=name=test'])->read();
print_r($out);

$out = $client->query(['/queue/simple/add', '=name=test'])->read();
print_r($out);
