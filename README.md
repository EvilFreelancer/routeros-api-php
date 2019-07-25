[![Latest Stable Version](https://poser.pugx.org/evilfreelancer/routeros-api-php/v/stable)](https://packagist.org/packages/evilfreelancer/routeros-api-php)
[![Build Status](https://travis-ci.org/EvilFreelancer/routeros-api-php.svg?branch=master)](https://travis-ci.org/EvilFreelancer/routeros-api-php)
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
RouterOS firmware, it will be detected automatically on connection stage.

## How to use

Basic example, analogue via command line is `/ip address print`:

```php
use \RouterOS\Client;

// Initiate client with config object
$client = new Client([
    'host' => '192.168.1.3',
    'user' => 'admin',
    'pass' => 'admin'
]);

// Send query to RouterOS and read response from it
$response = $client->query('/ip/address/print')->read();
var_dump($response);
```

Examples with "where" conditions, "operations" and "tag":

```php
use \RouterOS\Query;

/**
 * Send advanced query with parameters to RouterOS 
 */

// If only one "where" condition
$client->query('/queue/simple/print', ['target', '192.168.1.1/32']);

// If multiple "where" conditions and need merge (operation "|") results
$client->query('/interface/print', [
    ['type', 'ether'],  // same as ['type', '=', 'ether']
    ['type', 'vlan'],   // same as ['type', '=', 'vlan']
], '|');

/**
 * Or in OOP style
 */

// If multiple "where" conditions and need merge (operation "|") results
$query = new Query('/interface/print');
$query->where('type', 'ether');
$query->where('type', 'vlan');
$query->operations('|');

// If multiple "where" conditions and need append tag
$query = new Query('/interface/set');
$query->where('disabled', 'no');
$query->where('.id', 'ether1');
$query->tag('.tag=4');

/**
 * Write Query object to RouterOS and read response from it
 */

$response = $client->query($query)->read();
```

> All available examples you can find [here](https://github.com/EvilFreelancer/routeros-api-php/tree/master/examples).

## How to configure the client

You just need create object of Client class with required
parameters in array format:

```php
use \RouterOS\Client;

$client = new Client([
    'host' => '192.168.1.3',
    'user' => 'admin',
    'pass' => 'admin'
]);
```

<details>
<summary>
<i>ℹ️ Advanced examples of Config and Client classes usage</i>
</summary>

```php
use \RouterOS\Config;
use \RouterOS\Client;

/**
 * You can create object of Config class
 */

$config = new Config();

// Then set parameters of config
$config->set('host', '192.168.1.3');
$config->set('user', 'admin');
$config->set('pass', 'admin');

// By the way, `->set()` method is support inline style of syntax
$config
    ->set('host', '192.168.1.3')
    ->set('user', 'admin')
    ->set('pass', 'admin');

/**
 * Or just create preconfigured Config object
 */

$config = new Config([
    'host' => '192.168.1.3',
    'user' => 'admin',
    'pass' => 'admin'
]);

/**
 * Then send Config object to Client constructor
 */

$client = new Client($config);
```

</details>

### List of available configuration parameters

| Parameter | Type   | Default | Description |
|-----------|--------|---------|-------------|
| host      | string |         | (required) Address of Mikrotik RouterOS |
| user      | string |         | (required) Username |
| pass      | string |         | (required) Password |
| port      | int    |         | RouterOS API port number for access (if not set use 8728 or 8729 if SSL enabled) |
| ssl       | bool   | false   | Enable ssl support (if port is not set this parameter must change default port to ssl port) |
| legacy    | bool   | false   | Support of legacy login scheme (true - pre 6.43, false - post 6.43) |
| timeout   | int    | 10      | Max timeout for answer from RouterOS |
| attempts  | int    | 10      | Count of attempts to establish TCP session |
| delay     | int    | 1       | Delay between attempts in seconds |

### How to enable support of legacy login schema (RouterOS pre-6.43)

> From 0.8.1 this is not important, version of firmware will be detected automatically.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use \RouterOS\Client;

// Initiate client with config object
$client = new Client([
    'host'   => '192.168.1.3',
    'user'   => 'admin',
    'pass'   => 'admin',
    'legacy' => true // you need set `legacy` parameter with `true` value
]);

// Your code below...
```

## How to write queries

You can write absolutely any queries to your router, for this you
need to create a "Query" object whose first argument is the
required command, after this you can add the attributes of the
command to "Query" object.

More about attributes and "words" from which this attributes
should be created [here](https://wiki.mikrotik.com/wiki/Manual:API#Command_word). 

More about "expressions", "where" and other filters/modificators
of your query you can find [here](https://wiki.mikrotik.com/wiki/Manual:API#Queries).

Simple usage examples of Query class:

```php
use \RouterOS\Query;

// Get all installed packages (it may be enabled or disabled)
$query = new Query('/system/package/getall');

// Set where interface is disabled and ID is ether1 (with tag 4)
$query = 
    (new Query('/interface/set'))
        ->where('disabled', 'no')
        ->where('.id', 'ether1')
        ->tag(4);

// Get all ethernet and VLAN interfaces
$query = 
    (new Query('/interface/print'))
        ->where('type', 'ether')
        ->where('type', 'vlan')
        ->operations('|');

/// Get all routes that have non-empty comment
$query =
    (new Query('/ip/route/print'))
        ->where('comment', '>', null);
```

<details>
<summary>
<i>ℹ️ Advanced examples of Query class usage</i>
</summary>

```php
use \RouterOS\Query;
use \RouterOS\Client;

// Initiate connection to RouterOS
$client = new Client([
    'host'   => '192.168.1.3',
    'user'   => 'admin',
    'pass'   => 'admin'
]);

/**
 * Execute query directly through "->query()" method of Client class 
 */

// If your query has no "where" conditions
$client->query('/ip/arp/print');

// If you have only one where condition, you may use one dimensional array as second parameter of query method
$client->query('/queue/simple/print', ['target', '192.168.1.250/32']);

// If you need set few where conditions then need use multi dimensional array
$client->query('/interface/bridge/add', [
    ['name', 'vlan100-bridge'],
    ['vlan-filtering', 'no']
]);

/**
 * By some reason you may need restrict scope of RouterOS response,
 * for this need to use third attribute of "->query()" method
 */

// Get all ethernet and VLAN interfaces
$client->query('/interface/print', [
    ['type', 'ether'],
    ['type', 'vlan']
], '|');

/** 
 * If you want set tag of your query then you need to use fourth 
 * attribute of "->query()" method, but third attribute may be null
 */

// Enable interface (tag is 4)
$client->query('/interface/set', [
    ['disabled', 'no'],
    ['.id', 'ether1']
], null, 4);

/**
 * Or in OOP style  
 */

// Get all ethernet and VLAN interfaces
$query = new Query('/interface/print');
$query->where('type', 'ether');
$query->where('type', 'vlan');
$query->operations('|');

// Enable interface (tag is 4)
$query = new Query('/interface/set');
$query->where('disabled', 'no');
$query->where('.id', 'ether1');
$query->tag(4);

// Or

$query = new Query('/interface/set');
$query->add('=disabled=no');
$query->add('=.id=ether1');
$query->add('.tag=4');

// Or
    
$query = new Query('/interface/set', [
    '=disabled=no',
    '=.id=ether1',
    '.tag=4'
]);

// Or

$query = new Query([
    '/interface/set',
    '=disabled=no',
    '=.id=ether1',
    '.tag=4'
]);

/**
 * Write Query object to RouterOS and read response from it
 */

$response = $client->query($query)->read();
```

</details>

## Read response as Iterator

By default original solution of this client is not optimized for
work with large amount of results, only for small count of lines
in response from RouterOS API.

But some routers may have (for example) 30000+ records in
their firewall list. Specifically for such tasks, a method
`readAsIterator` has been added that converts the results
obtained from the router into a resource, with which it will
later be possible to work.

> You could treat response as an array except using any array_* functions

```php
$response = $client->query($query)->readAsIterator();
var_dump($response);

// The following for loop allows you to skip elements for which
// $iterator->current() throws an exception, rather than breaking
// the loop.
for ($response->rewind(); $response->valid(); $response->next()) {
    try {
        $value = $response->current();
    } catch (Exception $exception) {
        continue;
    }

    # ...
}
```

## Short methods

You can simplify your code and send then read from socket in one line:

```php
/** 
 * Execute query and read response in ordinary mode 
 */
$response = $client->query($query)->read();
var_dump($response);

// Or
$response = $client->q($query)->r();
var_dump($response);

// Single method analog of lines above is
$response = $client->qr($query);
var_dump($response);

/**
 * Execute query and read response as Iterator 
 */
$response = $client->query($query)->readAsIterator();
var_dump($response);

// Or
$response = $client->q($query)->ri();
var_dump($response);

// Single method analog of lines above is
$response = $client->qri($query);
var_dump($response);

/**
 * By the way, you can send few queries to your router without result: 
 */
$client->query($query1)->query($query2)->query($query3);

// Or
$client->q($query1)->q($query2)->q($query3);
```

## Testing

You can use my [other project](https://github.com/EvilFreelancer/docker-routeros)
with RouterOS in Docker container for running unit testing on your
computer, for this you just need to have [Expect](https://wiki.debian.org/Expect),
[Docker](https://docs.docker.com/install/) and [Docker Compose](https://docs.docker.com/compose/install/).

Next clone the repo with RouterOS in Docker and exec
`docker-compose up -d`, then you need preconfigure virtual routers
via [preconf.tcl](https://github.com/EvilFreelancer/routeros-api-php/blob/master/preconf.tcl)
script from root of routeros-api-php:

```
./preconf.tcl 12223
./preconf.tcl 22223
```

And after this you can run tests:

```
./vendor/bin/phpunit
```

## Links

* [Cloud Hosted Router](https://mikrotik.com/download#chr) - Virtual images of RouterOS for your hypervisor 
* [RouterOS Manual:API](https://wiki.mikrotik.com/wiki/Manual:API) - In case if you are wondering what is insane
