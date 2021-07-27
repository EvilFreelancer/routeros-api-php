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

/*
 * Create multiple users
 */

for ($i = 1; $i <= 1000; $i++) {

    // Build query for creating new user
    $query =
        (new Query('/ip/hotspot/user/profile/add'))
            ->equal('name', 'test-' . $i);

    // Create user on router
    $client->query($query);

    // Small timeout between requests is required
    sleep(.1);
}
// Small timeout
sleep(1);

// Close connection
unset($client);

// Create yet another socker connection
$client = new Client($config);

// Get list of all profiles
$query  = new Query('/ip/hotspot/user/profile/print');
$result = $client->query($query)->read();

dd($result);
