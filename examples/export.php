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
        ->set('pass', 'admin')
        ->set('user', 'admin')
        ->set('ssh_port', 22222);

// Initiate client with config object
$client = new Client($config);

// Execute export command via ssh
$response = $client->export();

print_r($response);
