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

// Build query for details about user profile
$query = new Query('/ip/hotspot/user/profile/print');

// Add user
$out = $client->query($query)->read();
dd($out);
