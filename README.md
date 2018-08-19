[![Latest Stable Version](https://poser.pugx.org/evilfreelancer/routeros-api-php/v/stable)](https://packagist.org/packages/evilfreelancer/routeros-api-php)
[![Total Downloads](https://poser.pugx.org/evilfreelancer/routeros-api-php/downloads)](https://packagist.org/packages/evilfreelancer/routeros-api-php)
[![License](https://poser.pugx.org/evilfreelancer/routeros-api-php/license)](https://packagist.org/packages/evilfreelancer/routeros-api-php)
[![Scrutinizer CQ](https://scrutinizer-ci.com/g/evilfreelancer/routeros-api-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/evilfreelancer/routeros-api-php/)

# RouterOS PHP7 API Client

    composer require evilfreelancer/routeros-api-php

This library is partly based on [this old project](https://github.com/BenMenking/routeros-api), but unlike it has many
innovations to ease development. In addition, the project is designed
to work with PHP7 in accordance with the PSR standards.

If you want to help the project, I will be glad to any help, my twitter [@EvilFreelancer](https://twitter.com/EvilFreelancer).

## Known issues

This library is not ready for production usage, because yet is not implemented new
login scheme for post 6.43 firmwares (but it works with pre 6.43).

In addition, need to implement a full test of everything through phpUnit, as
well as write more detailed documentation and add more examples.

This issues will be fixed in future releases.

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
