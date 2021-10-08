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

// Build query
// This assumes a list called "MyList" exists on your mikrotik, containing one or more entries.
$query = (new Query('/ip/firewall/address-list/find'))
	->where('list', 'MyList');
$ips = $client->query($query)->read();
$ips=$ips["after"]["ret"];

// Mikrotik responds with a list of entries, where each entry is delimited by ";"
// eg: "*65b;*65c;*66a"
// the delete function likes commas and NOT semicolons, so we replace ; for ,
// so we get "*65b,*65c,*66a"
$ips=str_replace(';', ',', $ips);
// now we just submit that with the remove command:
$query = (new Query('/ip/firewall/address-list/remove'))
	->equal('.id',$ips);
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
