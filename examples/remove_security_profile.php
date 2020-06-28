<?php
require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

// Create query which should remove security profile
$query = new \RouterOS\Query('/interface/wireless/security-profiles/remove');

// Here, instead of `->where()` need to use `->equal()`, it will generate queries,
// which stared from "=" symbol:
$query->where('.id', '*1');

$client   = new \RouterOS\Client(['host' => '192.168.88.1', 'user' => 'admin', 'pass' => 'password']);
$response = $client->query($query)->read();
