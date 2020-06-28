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
dump($response);

/*
// In results you will see something like this

# jun/28/2020 16:31:21 by RouterOS 6.47
# software id =
#
#
#
/interface wireless security-profiles
set [ find default=yes ] supplicant-identity=MikroTik
/ip dhcp-client
add disabled=no interface=ether1

 */

// But here is another example
$query = new Query('/export');

// Execute export command via ssh but in style of library
$response = $client->query($query)->read();
dump($response);
