<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use RouterOS\Client;

class ResponseIteratorTest extends TestCase
{
    public function testConstruct(): void
    {
        $obj = new Client([
            'user' => getenv('ROS_USER'),
            'pass' => getenv('ROS_PASS'),
            'host' => getenv('ROS_HOST'),
        ]);

        $obj = $obj->query('/system/package/print')->readAsIterator();
        $this->assertIsObject($obj);
    }

    public function testReadWrite(): void
    {
        $obj = new Client([
            'user' => getenv('ROS_USER'),
            'pass' => getenv('ROS_PASS'),
            'host' => getenv('ROS_HOST'),
        ]);

        $readTrap = $obj->query('/system/package/print')->readAsIterator();
        // Read from RAW
        $this->assertCount(13, $readTrap);

        $readTrap = $obj->query('/ip/address/print')->readAsIterator();
        $this->assertCount(1, $readTrap);
        $this->assertEquals('ether1', $readTrap[0]['interface']);

        $readTrap = $obj->query('/system/package/print')->readAsIterator();
        $key      = $readTrap->key();
        $this->assertEquals(0, $key);
        $current = $readTrap->current();
        $this->assertEquals('*1', $current['.id']);

        $readTrap->next();
        $key = $readTrap->key();
        $this->assertEquals(1, $key);
        $current = $readTrap->current();
        $this->assertEquals('*2', $current['.id']);

        $readTrap->prev();
        $key = $readTrap->key();
        $this->assertEquals(0, $key);
        $current = $readTrap->current();
        $this->assertEquals('*1', $current['.id']);

        $readTrap->prev(); // Check if key is not exist
        $key = $readTrap->key();
        $this->assertEquals(-1, $key);
        $current = $readTrap->current();
        $this->assertNull($current);
    }

    public function testSerialize(): void
    {
        $obj = new Client([
            'user' => getenv('ROS_USER'),
            'pass' => getenv('ROS_PASS'),
            'host' => getenv('ROS_HOST'),
        ]);

        $read = $obj->query('/queue/simple/print')->readAsIterator();
        $serialize = $read->serialize();
        $this->assertEquals('a:1:{i:0;a:1:{i:0;s:5:"!done";}}', $serialize);
    }

}
