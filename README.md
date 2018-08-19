# RouterOS PHP7 API Client

    composer require evilfreelancer/routeros-api-php

## Small example

Get all IP addresses, analog via command line is `/ip address print`

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

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
```

You can simplify your code and write then read from socket in one line:

```php
$response = $client->write($query)->read();
var_dump($response);
```

## Links

* [Cloud Hosted Router](https://mikrotik.com/download#chr) - Virtual images of RouterOS for your hypervisor 
* [RouterOS Manual:API](https://wiki.mikrotik.com/wiki/Manual:API) - In case if you are wondering what is insane
