<?php
require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

use \RouterOS\Client;

// Initiate client with config object
$client = new Client([
    'host'   => '192.168.5.1',
    'user'   => 'admin',
    'pass'   => 'admin',
    'legacy' => true
]);

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
    $response = $client->wr([
        '/interface/bridge/add',
        "=name=vlan$vlanId-bridge",
        'vlan-filtering=no'
    ]);
    print_r($response);

    // Add ports to bridge
    foreach ($ports as $port) {
        $response = $client->wr([
            '/interface/bridge/port/add',
            "=bridge=vlan$vlanId-bridge",
            "=pvid=$vlanId",
            "=interface=ether$port"
        ]);
        print_r($response);
    }

    // Add untagged ports to bridge with tagging
    foreach ($ports as $port) {
        $response = $client->wr([
            '/interface/bridge/vlan/add',
            "=bridge=vlan$vlanId-bridge",
            "=untagged=ether$port",
            "=vlan-ids=$vlanId"
        ]);
        print_r($response);
    }

}
