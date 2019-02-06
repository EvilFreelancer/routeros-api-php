[![Latest Stable Version](https://poser.pugx.org/evilfreelancer/routeros-api-php/v/stable)](https://packagist.org/packages/evilfreelancer/routeros-api-php)
[![Build Status](https://travis-ci.org/evilfreelancer/routeros-api-php.svg?branch=master)](https://travis-ci.org/EvilFreelancer/routeros-api-php)
[![Total Downloads](https://poser.pugx.org/evilfreelancer/routeros-api-php/downloads)](https://packagist.org/packages/evilfreelancer/routeros-api-php)
[![License](https://poser.pugx.org/evilfreelancer/routeros-api-php/license)](https://packagist.org/packages/evilfreelancer/routeros-api-php)
[![PHP 7 ready](https://php7ready.timesplinter.ch/EvilFreelancer/routeros-api-php/master/badge.svg)](https://travis-ci.org/EvilFreelancer/routeros-api-php)
[![Code Climate](https://codeclimate.com/github/EvilFreelancer/routeros-api-php/badges/gpa.svg)](https://codeclimate.com/github/EvilFreelancer/routeros-api-php)
[![Code Coverage](https://scrutinizer-ci.com/g/EvilFreelancer/routeros-api-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/EvilFreelancer/routeros-api-php/?branch=master)
[![Scrutinizer CQ](https://scrutinizer-ci.com/g/evilfreelancer/routeros-api-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/evilfreelancer/routeros-api-php/)

# RouterOS PHP7 API Client

    composer require evilfreelancer/routeros-api-php

This library is partly based on [this old project](https://github.com/BenMenking/routeros-api), but unlike it has many
innovations to ease development. In addition, the project is designed
to work with PHP7 in accordance with the PSR standards.

You can use this library with pre-6.43 and post-6.43 versions of
RouterOS firmware, for switching you just need set `legacy`
parameter of config to required state (`false` by default).

## How to use

### Basic example

More examples you can find [here](https://github.com/EvilFreelancer/routeros-api-php/tree/master/examples).

Get all IP addresses (analogue via command line is `/ip address print`):

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use \RouterOS\Config;
use \RouterOS\Client;
use \RouterOS\Query;

// Initiate client with config object
$client = new Client([
    'host' => '192.168.1.3',
    'user' => 'admin',
    'pass' => 'admin'
]);

// Build query
$query = new Query('/ip/address/print');

// Send query to RouterOS
$request = $client->write($query);

// Read answer from RouterOS
$response = $client->read();
var_dump($response);
```

You can simplify your code and send then read from socket in one line:

```php
$response = $client->write($query)->read();
var_dump($response);

// Single method analog of line above is
$response = $client->wr($query);
var_dump($response);
```

By the way, you can send few queries to your router without result:

```php
$client->write($query1)->write($query2)->write($query3);
```

### How to configure the client

```php
// Enable config class
use \RouterOS\Config;

// Create object of config class in one call
$config = new Config([
    'host' => '192.168.1.3',
    'user' => 'admin',
    'pass' => 'admin'
]);

// Create object of class
$config = new Config();

// Set parameters of config
$config->set('host', '192.168.1.3')
$config->set('user', 'admin')
$config->set('pass', 'admin');

// `set()` method supported inlines style of syntax
$config
    ->set('host', '192.168.1.3')
    ->set('user', 'admin')
    ->set('pass', 'admin');
```

Or you can just create preconfigured client object with all
required settings:

```php
// Enable config class
use \RouterOS\Client;

$client = new Client([
    'host' => '192.168.1.3',
    'user' => 'admin',
    'pass' => 'admin'
]);
```

#### List of available configuration parameters

| Parameter | Type   | Default | Description |
|-----------|--------|---------|-------------|
| host      | string |         | Address of Mikrotik RouterOS |
| user      | string |         | Username |
| pass      | string |         | Password |
| port      | int    |         | RouterOS API port number for access (if not set use 8728 or 8729 if SSL enabled) |
| ssl       | bool   | false   | Enable ssl support (if port is not set this parameter must change default port to ssl port) |
| legacy    | bool   | false   | Support of legacy login scheme (true - pre 6.43, false - post 6.43) |
| timeout   | int    | 10      | Max timeout for answer from RouterOS |
| attempts  | int    | 10      | Count of attempts to establish TCP session |
| delay     | int    | 1       | Delay between attempts in seconds |

### How to enable support of legacy login schema (RouterOS pre-6.43)

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use \RouterOS\Config;
use \RouterOS\Client;
use \RouterOS\Query;

// Create object of class and set parameters
$config =
    (new Config())
        ->set('host', '192.168.1.3')
        ->set('user', 'admin')
        ->set('pass', 'admin')
        ->set('legacy', true); // you need set `legacy` parameter with `true` value

// Initiate client with config object
$client = new Client($config);
......
```

### How to write queries

You can write absolutely any queries to your router, for this you
need to create a "Query" object whose first argument is the
required command, after this you can add the attributes of the
command to "Query" object.

More about attributes and "words" from which this attributes
should be created [here](https://wiki.mikrotik.com/wiki/Manual:API#Command_word). 

```php
use \RouterOS\Query;

// One line query: Get all packages
$query = new Query('/system/package/getall');

// Multiline query: Enable interface and add tag
$query = new Query('/interface/set');
$query
    ->add('=disabled=no')
    ->add('=.id=ether1')
    ->add('.tag=4');

// Multiline query: Get all ethernet and VLAN interfaces
$query = new Query('/interface/print');
$query
    ->add('?type=ether')
    ->add('?type=vlan')
    ->add('?#|');

// Multiline query: Get all routes that have non-empty comment
$query = new Query('/ip/route/print');
$query
    ->add('?>comment=');
```

## Links

* [Cloud Hosted Router](https://mikrotik.com/download#chr) - Virtual images of RouterOS for your hypervisor 
* [RouterOS Manual:API](https://wiki.mikrotik.com/wiki/Manual:API) - In case if you are wondering what is insane
