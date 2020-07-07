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

// Get list of all available profiles with name Block
$query = new Query('/ppp/secret/print');
$query->where('name', 'Block');
$secrets = $client->query($query)->read();

echo "Before update" . PHP_EOL;
print_r($secrets);

// Parse secrets and set password
foreach ($secrets as $secret) {

    // Change password
    $query = (new Query('/ppp/secret/set'))
        ->equal('.id', $secret['.id'])
        ->equal('password', 'pa$$word');

    // Update query ordinary have no return
    $client->query($query)->read();
}

// Get list of all available profiles with name Block
$query = new Query('/ppp/secret/print');
$query->where('name', 'Block');
$secrets = $client->query($query)->read();

echo PHP_EOL . "After update" . PHP_EOL;
print_r($secrets);
