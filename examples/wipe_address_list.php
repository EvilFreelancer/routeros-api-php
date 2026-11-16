<?php
require_once __DIR__ . '/../vendor/autoload.php';

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
/* THE POINT OF THIS EXAMPLE
 * 
 * If you have hundreds of entries in a list (or firewall rules or whatever)
 * you can delete them one by one in a for loop if you like,
 * but on higher latency links, this will take quite awhile, as the commands 
 * will be executed serially.
 * instead, you can ask for all the .ids of the list you want, and delete them all
 * in a single query as shown below
 */

// Create config object with parameters
$config =
    (new Config())
        ->set('host', '127.0.0.1')
        ->set('port', 8728)
        ->set('pass', 'admin')
        ->set('user', 'admin');

// Initiate client with config object
$client = new Client($config);

/*
 *  Make an array with all the existing entries in a specific list
 */

// Build a print query because RouterOS API supports query words on print commands.
$query = (new Query('/ip/firewall/address-list/print'))
    ->where('list', 'MyList');
$entries = $client->query($query)->read();
$ids = array_column($entries, '.id');

// Nothing needs to be removed when the filter returned no rows.
if ($ids === []) {
    exit(0);
}

// The remove command accepts a comma-separated list of internal IDs.
$query = (new Query('/ip/firewall/address-list/remove'))
    ->equal('.id', implode(',', $ids));
$ret = $client->query($query)->read();
if (isset($ret["after"]))
{
	/* this means an error happened, so print it and quit */
	echo $ret["after"]["message"];
	exit(1); 			
}
else
{
	// everything's fine
	exit (0);
}
?>
