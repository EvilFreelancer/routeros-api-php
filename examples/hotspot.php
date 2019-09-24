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
 * For the first we need to create new one user
 */

// Build query
$query =
    (new Query('/ip/hotspot/ip-binding/add'))
        ->equal('mac-address', '00:00:00:00:40:29')
        ->equal('type', 'bypassed')
        ->equal('comment', 'testcomment');

// Add user
$out = $client->query($query)->read();
print_r($out);

/*
 * Now try to remove created user from RouterOS
 */

// Remove user
$query =
    (new Query('/ip/hotspot/ip-binding/print'))
        ->where('mac-address', '00:00:00:00:40:29');

// Get user from RouterOS by query
$user = $client->query($query)->read();

if (!empty($user[0]['.id'])) {
    $userId     = $user[0]['.id'];

    // Remove MACa address
    $query =
        (new Query('/ip/hotspot/ip-binding/remove'))
            ->equal('.id', $userId);

    // Remove user from RouterOS
    $removeUser = $client->query($query)->read();
    print_r($removeUser);
}
