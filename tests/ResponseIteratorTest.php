<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use RouterOS\Client;

class ResponseIteratorTest extends TestCase
{
    public function test__construct()
    {
        $obj = new Client([
            'user' => getenv('ROS_USER'),
            'pass' => getenv('ROS_PASS'),
            'host' => getenv('ROS_HOST'),
        ]);

        $obj = $obj->write('/system/package/print')->readAsIterator();
        $this->assertIsObject($obj);
    }

    public function testReadWrite()
    {
        $obj = new Client([
            'user' => getenv('ROS_USER'),
            'pass' => getenv('ROS_PASS'),
            'host' => getenv('ROS_HOST'),
        ]);

        $readTrap = $obj->write('/system/package/print')->readAsIterator();
        // Read from RAW
        $this->assertCount(13, $readTrap);
        $this->assertEquals('advanced-tools', $readTrap[12]['name']);

        $readTrap = $obj->write('/ip/address/print')->readAsIterator();
        $this->assertCount(1, $readTrap);
        $this->assertEquals('ether1', $readTrap[0]['interface']);

        $readTrap = $obj->write('/system/package/print')->readAsIterator();
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

        $read = $obj->write('/queue/simple/print')->readAsIterator();
        $serialize = $read->serialize();
        $this->assertEquals('a:1:{i:0;a:1:{i:0;s:5:"!done";}}', $serialize);
    }

}
