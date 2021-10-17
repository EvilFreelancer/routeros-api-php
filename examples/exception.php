<?php
require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

use \RouterOS\Client;
use \RouterOS\Query;
use \RouterOS\Exceptions\ConnectException;

try {
    // Initiate client with config object
    $client = new Client([
        'timeout' => 1,
        'host'    => '127.0.0.1',
        'user'    => 'admin',
        'pass'    => 'admin'
    ]);
} catch (ConnectException $e) {
    echo $e->getMessage() . PHP_EOL;
    die();
}


// Build query
$query = new Query('/ip/address/print');

// Send query to RouterOS
$response = $client->query($query)->read();
print_r($response);
