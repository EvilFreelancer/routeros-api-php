<?php
require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

use \RouterOS\Config;
use \RouterOS\Client;
use \RouterOS\Query;

// Create config object with parameters
$config =
    (new Config())
        ->set('host', '127.0.0.1')
        ->set('user', 'admin')
        ->set('pass', 'admin');

// Initiate client with config object
$client = new Client($config);

// Build query
$query = new Query('/interface/getall');

// Send query to RouterOS
$request = $client->query($query);

// Read answer from RouterOS
$response = $client->read();
print_r($response);
