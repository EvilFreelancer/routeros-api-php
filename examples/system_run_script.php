<?php
require_once __DIR__ . '/../vendor/autoload.php';

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

// Create config object with parameters
$config =
    (new Config())
        ->set('host', '127.0.0.1')
        ->set('port', 8728)
        ->set('pass', 'admin')
        ->set('user', 'admin');

// Initiate client with config object
$client = new Client($config);

// Create script
$query = new Query('/system/script/add');
$query->equal('name', 'lala');
$query->equal('source', ':put "lala"');

// Read results
$results = $client->query($query)->read();
dump($results);

// Run script
$query = new Query('/system/script/run');
$query->equal('number', 'lala');

// Read results
$results = $client->query($query)->read();

dd($results);
