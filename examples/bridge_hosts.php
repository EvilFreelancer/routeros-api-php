<?php
require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

use \RouterOS\Config;
use \RouterOS\Client;
use \RouterOS\Query;

// Create config object with parameters
$config =
    (new Config())
        ->set('timeout', 1)
        ->set('host', '127.0.0.1')
        ->set('user', 'admin')
        ->set('pass', 'admin');

// Initiate client with config object
$client = new Client($config);

// Build query
$query = new Query('/interface/bridge/host/print');

// Send query to RouterOS
$response = $client->write($query)->read();
print_r($response);
