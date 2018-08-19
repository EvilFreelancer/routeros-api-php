<?php
require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

use \RouterOS\Config;
use \RouterOS\Client;
use \RouterOS\Query;

/**
 * Set the params
 */
$config = new Config();
$config->host = '192.168.1.104';
$config->user = 'admin';
$config->pass = 'admin';

/**
 * Initiate client with parameters
 */
$client = new Client($config);

/**
 * Build query
 */
$query = new Query('/ip/address/print');

/**
 * Send query to socket server
 */
$request = $client->write($query);
var_dump($request);

/**
 * Read answer from server
 */
$response = $client->read();
var_dump($response);
