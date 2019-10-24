<?php

/**
 * This examples created by https://github.com/EvilFreelancer/routeros-api-php/issues/20 issue
 */

require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

use RouterOS\Client;
use RouterOS\Query;

$client = new Client([
    'timeout' => 1,
    'host'    => '127.0.0.1',
    'user'    => 'admin',
    'pass'    => 'admin'
]);

/*
 * Create script for automation
 */

// Wrong way (RouterOS don't understand line termination symbol)
$query =
    (new Query('/system/script/add'))
        ->equal('name', 'test_by_system1')
        ->equal('source', 'monitoring')
        ->equal('source', ':local monitor [/interface monitor-traffic ether2 as-value once] \r\n
:local speedRX ($monitor->"rx-bits-per-second")\r\n
:local speedTX($monitor->"tx-bits-per-second")\r\n
/tool fetch mode=http url=("http://192.168.254.72/micro-nms/storebw/1/$speedTX/$speedRX") keep-result=no');

$response = $client->query($query)->read();
print_r($response);

// Okay way (command in one line)
$query =
    (new Query('/system/script/add'))
        ->equal('name', 'test_by_system2')
        ->equal('source', 'monitoring')
        ->equal('source',
            ':local monitor [/interface monitor-traffic ether2 as-value once] \r\n:local speedRX ($monitor->"rx-bits-per-second")\r\n:local speedTX($monitor->"tx-bits-per-second")\r\n/tool fetch mode=http url=("http://192.168.254.72/micro-nms/storebw/1/$speedTX/$speedRX") keep-result=no');

$response = $client->query($query)->read();
print_r($response);

// Best practice
$query =
    (new Query('/system/script/add'))
        ->equal('name', 'test_by_system3')
        ->equal('source', 'monitoring')
        ->equal('source', ':local monitor [/interface monitor-traffic ether2 as-value once] \r\n'
            . ':local speedRX ($monitor->"rx-bits-per-second")\r\n'
            . ':local speedTX ($monitor->"tx-bits-per-second")\r\n'
            . '/tool fetch mode=http url=("http://192.168.254.72/micro-nms/storebw/1/$speedTX/$speedRX") keep-result=no');

$response = $client->query($query)->read();
print_r($response);

// Create scheduler for triggering created script
$query =
    (new Query('/system/scheduler/add'))
        ->equal('name', 'monitoring')
        ->equal('on-event', 'monitoring')
        ->equal('interval', '1s')
        ->equal('start-time', 'startup');

$response = $client->query($query)->read();
print_r($response);

// Print created scripts
$query = new Query('/system/script/print');

$response = $client->query($query)->read();
print_r($response);
