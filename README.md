[![Latest Stable Version](https://poser.pugx.org/evilfreelancer/routeros-api-php/v/stable)](https://packagist.org/packages/evilfreelancer/routeros-api-php)
[![Total Downloads](https://poser.pugx.org/evilfreelancer/routeros-api-php/downloads)](https://packagist.org/packages/evilfreelancer/routeros-api-php)
[![License](https://poser.pugx.org/evilfreelancer/routeros-api-php/license)](https://packagist.org/packages/evilfreelancer/routeros-api-php)

# RouterOS API Client

```shell
composer require evilfreelancer/routeros-api-php
```

This library is partly based on [this old project](https://github.com/BenMenking/routeros-api), but unlike it has many
innovations to ease development. In addition, the project designed
to work with PHP7/8 in accordance with the PSR standards.

You can use this library with pre-6.43 and post-6.43 versions of
RouterOS firmware, it will be detected automatically on connection stage.

## Table of Contents

* [Minimum requirements](#minimum-requirements)
* [Laravel framework support](#laravel-framework-support)
  * [Laravel installation](#laravel-installation)
* [How to use](#how-to-use)
* [How to configure the client](#how-to-configure-the-client)
  * [List of available configuration parameters](#list-of-available-configuration-parameters)
  * [Legacy login before RouterOS 6.43](#how-to-enable-support-of-legacy-login-schema-routeros-pre-643)
* [How to write queries](#how-to-write-queries)
  * [Regular expression filters](#regular-expression-filters)
  * [Detailed print output](#detailed-print-output)
* [Read response as Iterator](#read-response-as-iterator)
* [Short methods](#short-methods)
* [Common tasks and troubleshooting](#common-tasks-and-troubleshooting)
  * [Create, update, or remove an item](#create-update-or-remove-an-item)
  * [Count rows without printing every row](#count-rows-without-printing-every-row)
  * [Read complete multiline scripts and file contents](#read-complete-multiline-scripts-and-file-contents)
  * [Run a RouterOS script](#run-a-routeros-script)
  * [Avoid hanging on monitor commands](#avoid-hanging-on-monitor-commands)
  * [Handle RouterOS command errors](#handle-routeros-command-errors)
  * [Use a domain name, custom port, or several routers](#use-a-domain-name-custom-port-or-several-routers)
  * [Fix connection refused and timeout errors](#fix-connection-refused-and-timeout-errors)
  * [Export configuration through SSH](#export-configuration-through-ssh)
  * [Reboot and reconnect](#reboot-and-reconnect)
  * [Encoding and non-English text](#encoding-and-non-english-text)
* [Testing](#testing)
* [Links](#links)

## Minimum requirements

* `php` >= 7.4 or 8.x
* `ext-sockets`

## Laravel framework support

RouterOS API client is optimized for usage as normal Laravel package, all functional is available via `\RouterOS` facade,
for access to client object you need instead:

```php
$config = new \RouterOS\Config([
    'host' => '192.168.1.3',
    'user' => 'admin',
    'pass' => 'admin',
    'port' => 8728,
]);
$client = new \RouterOS\Client($config);
```

Use the facade and pass array of parameters to `client` method:

```php
$client = \RouterOS::client([
    'host' => '192.168.1.3',
    'user' => 'admin',
    'pass' => 'admin',
    'port' => 8728,
]);
```

You also may get array with all configs which was obtained from `routeros-api.php` file:

```php
$config = \RouterOS::config([
    'host' => '192.168.1.3',
    'user' => 'admin',
    'pass' => 'admin',
    'port' => 8728,
]);

dump($config);

$client = \RouterOS::client($config);
```

### Laravel installation

By default, the package will automatically register its service provider, but
if you are a happy owner of Laravel version less than 5.5, then in a project, which is using the package
(after `composer require` is done, of course), add into`providers` block of your `config/app.php`:

```php
'providers' => [
    // ...
    RouterOS\Laravel\ServiceProvider::class,
],
```

#### Client configuration for Laravel

You could configure the Client using Environment variables. Complete list of available Environment variables you could find at the [default configuration file](https://github.com/EvilFreelancer/routeros-api-php/blob/master/configs/routeros-api.php).

Optionally you could publish the configuration file:

```shell
php artisan vendor:publish --provider="RouterOS\\Laravel\\ServiceProvider"
```

## How to use

Basic example, analogue via command line is `/ip hotspot ip-binding print`:

```php
use \RouterOS\Client;
use \RouterOS\Query;

// Initiate client with config object
$client = new Client([
    'host' => '192.168.1.3',
    'user' => 'admin',
    'pass' => 'admin',
    'port' => 8728,
]);

// Create "where" Query object for RouterOS
$query =
    (new Query('/ip/hotspot/ip-binding/print'))
        ->where('mac-address', '00:00:00:00:40:29');

// Send query and read response from RouterOS
$response = $client->query($query)->read();

var_dump($response);
```

Basic example for update/create/delete types of queries:

```php
use \RouterOS\Client;
use \RouterOS\Query;

// Initiate client with config object
$client = new Client([
    'host' => '192.168.1.3',
    'user' => 'admin',
    'pass' => 'admin'
]);

// Send "equal" query with details about IP address which should be created
$query =
    (new Query('/ip/hotspot/ip-binding/add'))
        ->equal('mac-address', '00:00:00:00:40:29')
        ->equal('type', 'bypassed')
        ->equal('comment', 'testcomment');

// Send query and read response from RouterOS (ordinary answer from update/create/delete queries has empty body)
$response = $client->query($query)->read();

var_dump($response);
```

If you need export all settings from router:

```php
use \RouterOS\Client;

// Initiate client with config object
$client = new Client([
    'host'        => '192.168.1.3',
    'user'        => 'admin',
    'pass'        => 'admin',
    'ssh_port'    => 22222,
    'ssh_timeout' => 60, // if not set then 30 seconds by default 
]);

// Execute export command via ssh
$response = $client->query('/export');
// or
$response = $client->export();

var_dump($response);
``` 

Examples with "where" conditions, "operations" and "tag":

```php
use \RouterOS\Query;

/**
 * Simple "where" query will be generated by default 
 */

$client->query('/ip/address/print')->read();

/**
 * Send advanced "where" query with parameters to RouterOS 
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

// If you need create query for "create/update/delete" operations
$query = new Query('/ip/hotspot/ip-binding/add');
$query->equal('mac-address', '00:00:00:00:40:29');
$query->equal('type', 'bypassed');
$query->equal('comment', 'testcomment');

// If multiple "where" conditions and need merge (operation "|") results
$query = new Query('/interface/print');
$query->where('type', 'ether');
$query->where('type', 'vlan');
$query->operations('|');

// If multiple "where" conditions and need append tag
$query = new Query('/interface/set');
$query->where('disabled', 'no');
$query->where('.id', 'ether1');
$query->tag(4);

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

| Parameter       | Type   | Default                                                                                      | Description                                                                                                 |
|-----------------|--------|----------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------|
| host            | string |                                                                                              | (required) Address of Mikrotik RouterOS                                                                     |
| user            | string |                                                                                              | (required) Username                                                                                         |
| pass            | string |                                                                                              | (required) Password                                                                                         |
| port            | int    |                                                                                              | RouterOS API port number for access (if not set use 8728 or 8729 if SSL enabled)                            |
| ssl             | bool   | false                                                                                        | Enable ssl support (if port is not set this parameter must change default port to ssl port)                 |
| ssl_options     | array  | [details](https://github.com/EvilFreelancer/routeros-api-php/blob/master/src/Config.php#L46) | See https://www.php.net/manual/en/context.ssl.php                                                           |
| legacy          | bool   | false                                                                                        | Deprecated, will be removed from 1.5.0: Support of legacy login scheme (true - pre 6.43, false - post 6.43) |
| timeout         | int    | 10                                                                                           | Max timeout for connecting to RouterOS (in seconds)                                                         |
| socket_timeout  | int    | 30                                                                                           | Max read timeout from RouterOS (in seconds)                                                                 |
| socket_blocking | bool   | true                                                                                         | Set blocking mode on a socket stream                                                                        |
| socket_options  | array  | [details](https://github.com/EvilFreelancer/routeros-api-php/blob/master/src/Config.php#L87) | See https://www.php.net/manual/en/context.socket.php                                                        |
| throw_timeout_exception | bool | true | Throw StreamException when a socket read times out |
| attempts        | int    | 10 | Count of attempts to establish TCP session                                                                  |
| delay           | int    | 1                                                                                            | Delay between attempts in seconds                                                                           |
| ssh_port        | int    | 22                                                                                           | Number of SSH port for exporting configuration                                                              |
| ssh_timeout     | int    | 30                                                                                           | Max timeout from router via SSH (in seconds)                                                                |
| ssh_private_key | string | ~/.ssh/id_rsa                                                                                | Full path to required private key                                                                           |

### How to enable support of legacy login schema (RouterOS pre-6.43)

> From 0.8.1 this is not important, version of firmware will be detected automatically.
> Deprecated, will be removed from 1.5.0

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

More about attributes and "words" from which these attributes
should be created [here](https://wiki.mikrotik.com/wiki/Manual:API#Command_word). 

More about "expressions", "where", "equal" and other filters/modifications
of your query you can find [here](https://wiki.mikrotik.com/wiki/Manual:API#Queries).

Simple usage examples of Query class:

```php
use \RouterOS\Query;

// Get all installed packages (it may be enabled or disabled)
$query = new Query('/system/package/getall');

// Send "equal" query with details about IP address which should be created
$query =
    (new Query('/ip/hotspot/ip-binding/add'))
        ->equal('mac-address', '00:00:00:00:40:29')
        ->equal('type', 'bypassed')
        ->equal('comment', 'testcomment');

// Set where interface is disabled and ID is ether1 (with tag 4)
$query = 
    (new Query('/interface/set'))
        ->equal('disabled', 'no')
        ->equal('.id', 'ether1')
        ->tag(4);

// Get all ethernet and VLAN interfaces
$query = 
    (new Query('/interface/print'))
        ->where('type', 'ether')
        ->where('type', 'vlan')
        ->operations('|');

// Get all routes that have non-empty comment
$query =
    (new Query('/ip/route/print'))
        ->where('comment', '>', '');
```

### Regular expression filters

RouterOS CLI supports the `~` operator, but the RouterOS API does not. Sending `?~` query words is not a supported workaround. Use an exact API query where possible, then apply regular expression or substring filtering to the returned rows in PHP. To reduce response size, request only the required properties with `.proplist`.

```php
$query = new Query('/ip/firewall/connection/print', [
    '.proplist=.id,src-address,dst-address',
]);
$connections = $client->query($query)->read();
$matches = array_filter($connections, static function (array $connection): bool {
    $host = explode(':', $connection['dst-address'] ?? '')[0];
    return $host === '192.168.1.200';
});
```

### Detailed print output

Pass RouterOS-specific print arguments as raw attributes:

```php
$query = new Query('/ppp/secret/print', ['=detail=']);
$response = $client->query($query)->read();
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

// Command attributes also use equal() when creating an item
$query = (new Query('/interface/bridge/add'))
    ->equal('name', 'vlan100-bridge')
    ->equal('vlan-filtering', 'no');

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
$query = (new Query('/interface/set'))
    ->equal('disabled', 'no')
    ->equal('.id', 'ether1')
    ->tag(4);
$client->query($query);

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
$query->equal('disabled', 'no');
$query->equal('.id', 'ether1');
$query->tag(4);

// Or, RAW mode

$query = new Query('/interface/set');
$query->add('=disabled=no');
$query->add('=.id=ether1');
$query->add('.tag=4');

// Or, RAW mode in format of array
    
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

By default, original solution of this client is not optimized for
work with a large amount of results, only for small count of lines
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

## Common tasks and troubleshooting

### Create, update, or remove an item

Use `where()` only for filtering `print` responses. Use `equal()` for command attributes, including the `.id` used by `set` and `remove`.

```php
$query = (new \RouterOS\Query('/ip/address/remove'))
    ->equal('.id', '*1');
$response = $client->query($query)->read();
```

The same rule applies to PPP secrets, active PPP/HotSpot sessions, queues, address lists, interface settings, and other RouterOS commands.

### Count rows without printing every row

`count-only` is a command attribute. Its result is returned in the `ret` field of the final response.

```php
$query = (new Query('/ip/hotspot/user/print'))->equal('count-only');
$response = $client->query($query)->read();
$count = (int) ($response['after']['ret'] ?? 0);
```

### Read complete multiline scripts and file contents

Multiline attributes are returned as one PHP string, including LF and CRLF line breaks. Do not concatenate rows or switch to `read(false)` just to retrieve the complete value.

```php
$query = (new Query('/system/script/print'))->where('name', 'my-script');
$response = $client->query($query)->read();
$source = $response[0]['source'] ?? '';
```

### Run a RouterOS script

Pass the script name or number as the `number` attribute of `/system/script/run`.

```php
$query = (new Query('/system/script/run'))->equal('number', 'my-script');
$response = $client->query($query)->read();
```

### Avoid hanging on monitor commands

Continuous commands such as `monitor-traffic`, `listen`, and `torch` do not produce a final reply on their own. Prefer a finite command when one is available. For example, `monitor-traffic` supports `once` and requires an interface.

```php
$query = new Query('/interface/monitor-traffic', [
    '=interface=ether1',
    '=once=',
]);
$response = $client->query($query)->read();
```

For `/ping`, set its own `count` attribute. For a continuous listener, use tags and the RouterOS `/cancel` command instead of leaving `read()` waiting indefinitely.

### Handle RouterOS command errors

Connection, configuration, stream, and query-building failures throw exceptions. A command accepted by RouterOS but rejected during execution normally returns a `!trap` response instead of throwing an exception. The parsed message is available under `after.message`.

```php
$response = $client->query('/not/a/command')->read();
if (isset($response['after']['message'])) {
    throw new RuntimeException($response['after']['message']);
}
```

Use `read(false)` when you need to inspect raw reply words such as `!trap`, `!fatal`, and `!done`.

| Failure stage | Exception or result |
| --- | --- |
| Invalid or missing configuration | `\RouterOS\Exceptions\ConfigException` |
| TCP connection or TLS negotiation | `\RouterOS\Exceptions\ConnectException` |
| RouterOS rejects login credentials | `\RouterOS\Exceptions\BadCredentialsException` |
| Socket read, write, EOF, or timeout | `\RouterOS\Exceptions\StreamException` |
| Invalid query endpoint or operator | `\RouterOS\Exceptions\QueryException` |
| Read loop or SSH export failure | `\RouterOS\Exceptions\ClientException` |
| RouterOS rejects a valid command | `!trap`, exposed as `after.message` |

### Use a domain name, custom port, or several routers

`host` accepts an IP address or DNS hostname without a URL scheme. Set `port` explicitly when the API is not exposed on 8728 or 8729. Create a separate `Client` for every router instead of changing shared connection state.

```php
$client = new Client([
    'host' => 'router.example.net',
    'port' => 8790,
    'user' => 'api-user',
    'pass' => 'secret',
    'timeout' => 5,
    'socket_timeout' => 30,
]);
```

### Fix connection refused and timeout errors

`Connection refused` happens before authentication and usually means that no API listener accepted the TCP connection. Check the following from the machine or container running PHP:

* Enable `/ip service set api disabled=no`, or configure `api-ssl` when `ssl` is enabled;
* Allow the API port in RouterOS firewall and in intermediate firewalls;
* Check that `host` and `port` are reachable from production, not only from your workstation;
* Do not use `127.0.0.1` inside a container when the router is running on the Docker host or in another container;
* Set `timeout` for connection establishment and `socket_timeout` for reads independently.

`socket_blocking=true` is recommended for normal request/response usage. Set `throw_timeout_exception=false` only when an application deliberately handles incomplete or interrupted reads itself. Reducing `attempts` and `delay` bounds the time spent reconnecting, but does not repair an unreachable service. SSH export has a separate `ssh_timeout` setting.

### Export configuration through SSH

`/export` and `$client->export()` use SSH, not the RouterOS API port. Configure `ssh_port`, `ssh_timeout`, and `ssh_private_key`. The API password is not used for SSH password authentication.

```php
$client = new Client([
    'host' => '192.168.88.1',
    'user' => 'admin',
    'pass' => 'api-password',
    'ssh_private_key' => '/home/app/.ssh/id_rsa',
]);
$export = $client->export();
```

Import the corresponding public key for that RouterOS user, enable SSH, and verify the connection manually. PHP also needs permission to run the system `ssh` client, which can be restricted on shared hosting.

### Reboot and reconnect

A reboot closes the API session and can legitimately produce no data rows. The library does not automatically reconnect after reboot. Wait until the router and API service are available, then create a new `Client`.

### Encoding and non-English text

API words are binary-safe and the client preserves multibyte values. Use UTF-8 consistently in PHP source files, HTML output, logs, and database connections. If only a terminal or web page displays broken characters, inspect the raw API value and fix the output encoding instead of changing the stored text.

## Testing

Unit tests run in Docker and do not require a RouterOS device. Docker Compose uses a separate `vendor` volume for each PHP version.

```shell
export PHP_VERSION=8.4
docker compose build php
docker compose run --rm php composer update --no-interaction --prefer-dist
docker compose run --rm php vendor/bin/phpunit
docker compose run --rm php vendor/bin/phpunit --coverage-text
```

Use `composer update` when switching PHP versions because the library does not commit a shared `composer.lock`. Each version must resolve its own compatible development dependencies.

Run the supported PHP matrix sequentially:

```shell
for version in 7.4 8.0 8.1 8.2 8.3 8.4 8.5; do
    export PHP_VERSION=$version
    docker compose build php || exit 1
    docker compose run --rm php sh -c \
        'composer update --no-interaction --prefer-dist && vendor/bin/phpunit' || exit 1
done
```

Integration tests use [docker-routeros](https://github.com/EvilFreelancer/docker-routeros) and require Expect plus `/dev/net/tun`. The RouterOS management ports are published on the host loopback address only.

```shell
export PHP_VERSION=8.4
docker compose up -d routeros
```

Wait for RouterOS to boot, then configure its test credentials. On a slow host, retry after 10 seconds if the guest is not ready yet.

```shell
./preconf.tcl 12223
```

Run the integration suite through the Compose network:

```shell
docker compose run --rm \
    -e ROS_HOST_MODERN=routeros \
    -e ROS_HOST_LEGACY=routeros \
    -e ROS_PORT_MODERN=8728 \
    -e ROS_PORT_LEGACY=8728 \
    php vendor/bin/phpunit --configuration phpunit.integration.xml
```

Stop the virtual router and remove its containers when finished:

```shell
docker compose down
```

Add `-v` to that command only when you also want to delete the RouterOS disk and all cached dependency volumes.

## Links

* [Cloud Hosted Router](https://mikrotik.com/download#chr) - Virtual images of RouterOS for your hypervisor 
* [RouterOS Manual:API](https://wiki.mikrotik.com/wiki/Manual:API) - In case if you are wondering what is insane
