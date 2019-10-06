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

// Build monitoring query
$query =
    (new Query('/interface/monitor-traffic'))
        ->equal('interface', 'ether1')
        ->equal('once');

// Ask for monitoring details
$out = $client->query($query)->read();
print_r($out);
