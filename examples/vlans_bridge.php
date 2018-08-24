<?php
require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

use \RouterOS\Config;
use \RouterOS\Client;
use \RouterOS\Query;

// Create config object with parameters
$config =
    (new Config())
        ->set('host', '192.168.5.1')
        ->set('pass', 'admin')
        ->set('user', 'admin')
        ->set('legacy', true);

// Initiate client with config object
$client = new Client($config);

/*
 * Create VLAN 100 on 3-8 ports
 *             200 on 9-16
 *             300 on 17-24
 */
$vlans = [
    100 => [3, 4, 5, 6, 7, 8],
    200 => [9, 10, 11, 12, 13, 14, 15, 16],
    300 => [17, 18, 19, 20, 21, 22, 23, 24],
];

// Run commands for each vlan
foreach ($vlans as $vlanId => $ports) {

    // Add bridges
    $query = new Query('/interface/bridge/add');
    $query->add("=name=vlan$vlanId-bridge")->add('vlan-filtering=no');
    $response = $client->write($query)->read();
    print_r($response);

    // Add ports to bridge
    foreach ($ports as $port) {
        $bridgePort = new Query('/interface/bridge/port/add');
        $bridgePort->add("=bridge=vlan$vlanId-bridge")->add("=pvid=$vlanId")->add("=interface=ether$port");
        $response = $client->write($bridgePort)->read();
        print_r($response);
    }

    // Add untaged ports to bridge with tagging
    foreach ($ports as $port) {
        $vlan = new Query('/interface/bridge/vlan/add');
        $vlan->add("=bridge=vlan$vlanId-bridge")->add("=untagged=ether$port")->add("=vlan-ids=5");
        $response = $client->write($vlan)->read(false);
        print_r($response);
    }

}
