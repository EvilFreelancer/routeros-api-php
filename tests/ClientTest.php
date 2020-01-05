<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use RouterOS\Client;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;
use RouterOS\Config;
use RouterOS\Exceptions\ClientException;

class ClientTest extends TestCase
{
    /**
     * @var array
     */
    public $router;

    /**
     * @var int
     */
    public $port_modern;

    /**
     * @var int
     */
    public $port_legacy;

    public function setUp()
    {
        parent::setUp();

        $this->router = [
            'user' => getenv('ROS_USER'),
            'pass' => getenv('ROS_PASS'),
            'host' => getenv('ROS_HOST'),
        ];

        $this->port_modern = (int) getenv('ROS_PORT_MODERN');
        $this->port_legacy = (int) getenv('ROS_PORT_LEGACY');
    }

    public function test__construct(): void
    {
        try {
            $config = new Config();
            $config
                ->set('user', $this->router['user'])
                ->set('pass', $this->router['pass'])
                ->set('host', $this->router['host']);

            $obj = new Client($config);
            $this->assertIsObject($obj);
            $socket = $obj->getSocket();
            $this->assertIsResource($socket);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

    public function test__construct2(): void
    {
        try {
            $config = new Config($this->router);
            $obj    = new Client($config);
            $this->assertIsObject($obj);
            $socket = $obj->getSocket();
            $this->assertIsResource($socket);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

    public function test__construct3(): void
    {
        try {
            $obj = new Client($this->router);
            $this->assertIsObject($obj);
            $socket = $obj->getSocket();
            $this->assertIsResource($socket);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

    public function test__constructEx(): void
    {
        $this->expectException(ConfigException::class);

        $obj = new Client([
            'user' => $this->router['user'],
            'pass' => $this->router['pass'],
        ]);
    }

    public function test__constructLegacy(): void
    {
        try {
            $obj = new Client([
                'user'   => $this->router['user'],
                'pass'   => $this->router['pass'],
                'host'   => $this->router['host'],
                'port'   => $this->port_legacy,
                'legacy' => true
            ]);
            $this->assertIsObject($obj);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

    /**
     * Test non legacy connection on legacy router (pre 6.43)
     *
     * login() method recognise legacy router response and swap to legacy mode
     */
    public function test__constructLegacy2(): void
    {
        try {
            $obj = new Client([
                'user'   => $this->router['user'],
                'pass'   => $this->router['pass'],
                'host'   => $this->router['host'],
                'port'   => $this->port_legacy,
                'legacy' => false
            ]);
            $this->assertIsObject($obj);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }


    public function test__constructWrongPass(): void
    {
        $this->expectException(ClientException::class);

        $obj = new Client([
            'user'     => $this->router['user'],
            'pass'     => 'admin2',
            'host'     => $this->router['host'],
            'attempts' => 2
        ]);
    }

    /**
     * @expectedException ClientException
     */
    public function test__constructWrongNet(): void
    {
        $this->expectException(ClientException::class);

        $obj = new Client([
            'user'     => $this->router['user'],
            'pass'     => $this->router['pass'],
            'host'     => $this->router['host'],
            'port'     => 11111,
            'attempts' => 2
        ]);
    }

    public function testQueryRead(): void
    {
        $config = new Config();
        $config
            ->set('user', $this->router['user'])
            ->set('pass', $this->router['pass'])
            ->set('host', $this->router['host']);

        $obj = new Client($config);

        /*
         * Build query with where
         */

        $read = $obj->query('/system/package/print', ['name'])->read();
        $this->assertCount(13, $read);

        $read = $obj->query('/system/package/print', ['.id', '*1'])->read();
        $this->assertCount(1, $read);

        $read = $obj->query('/system/package/print', ['.id', '=', '*1'])->read();
        $this->assertCount(1, $read);

        $read = $obj->query('/system/package/print', [['name']])->read();
        $this->assertCount(13, $read);

        $read = $obj->query('/system/package/print', [['.id', '*1']])->read();
        $this->assertCount(1, $read);

        $read = $obj->query('/system/package/print', [['.id', '=', '*1']])->read();
        $this->assertCount(1, $read);

        /*
         * Build query with operations
         */

        $read = $obj->query('/interface/print', [
            ['type', 'ether'],
            ['type', 'vlan']
        ], '|')->read();
        $this->assertCount(1, $read);
        $this->assertEquals('*1', $read[0]['.id']);

        /*
         * Build query with tag
         */

        $read = $obj->query('/system/package/print', null, null, 'zzzz')->read();
        $this->assertCount(13, $read);
        $this->assertEquals('zzzz', $read[0]['tag']);
    }

    public function testReadAsIterator(): void
    {
        $obj = new Client($this->router);

        $obj = $obj->write('/system/package/print')->readAsIterator();
        $this->assertIsObject($obj);
    }

    public function testWriteReadString(): void
    {
        $obj = new Client([
            'user' => $this->router['user'],
            'pass' => $this->router['pass'],
            'host' => $this->router['host'],
        ]);

        $readTrap = $obj->wr('/interface', false);
        $this->assertCount(3, $readTrap);
        $this->assertEquals('!trap', $readTrap[0]);
    }

    public function testFatal(): void
    {
        $obj = new Client([
            'user' => $this->router['user'],
            'pass' => $this->router['pass'],
            'host' => $this->router['host'],
        ]);

        $readTrap = $obj->query('/quit')->read();
        $this->assertCount(2, $readTrap);
        $this->assertEquals('!fatal', $readTrap[0]);
    }

    public function testQueryEx1(): void
    {
        $this->expectException(ClientException::class);

        $obj = new Client([
            'user' => $this->router['user'],
            'pass' => $this->router['pass'],
            'host' => $this->router['host'],
        ]);

        $obj->query('/quiet', ['a', 'b', 'c', 'd']);
    }

    public function testQueryEx2(): void
    {
        $this->expectException(ClientException::class);

        $obj = new Client([
            'user' => $this->router['user'],
            'pass' => $this->router['pass'],
            'host' => $this->router['host'],
        ]);

        $obj->query('/quiet', [[]]);
    }
}
