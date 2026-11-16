<?php

namespace RouterOS\Tests\Integration;

use PHPUnit\Framework\TestCase;
use RouterOS\Client;

class ResponseIteratorTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp(): void
    {
        $this->client = new Client([
            'user' => getenv('ROS_USER'),
            'pass' => getenv('ROS_PASS'),
            'host' => getenv('ROS_HOST_MODERN'),
        ]);
    }

    public function testReadWrite(): void
    {
        $readTrap = $this->client->query('/system/logging/print')->readAsIterator();
        $this->assertNotEmpty($readTrap);

        $readTrap = $this->client->query('/ip/address/print')->readAsIterator();
        $this->assertCount(1, $readTrap);
        $this->assertEquals('ether1', $readTrap[0]['interface']);

        $readTrap = $this->client->query('/system/logging/print')->readAsIterator();
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
        $read     = $this->client->query('/queue/simple/print')->readAsIterator();
        $restored = unserialize(serialize($read));

        $this->assertEquals(iterator_to_array($read), iterator_to_array($restored));
    }
}
