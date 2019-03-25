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
    public function test__construct()
    {
        try {
            $config = new Config();
            $config->set('user', getenv('ROS_USER'))->set('pass', getenv('ROS_PASS'))->set('host', getenv('ROS_HOST'));
            $obj = new Client($config);
            $this->assertInternalType('object', $obj);
            $socket = $obj->getSocket();
            $this->assertInternalType('resource', $socket);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

    public function test__construct2()
    {
        try {
            $config = new Config([
                'user' => getenv('ROS_USER'),
                'pass' => getenv('ROS_PASS'),
                'host' => getenv('ROS_HOST')
            ]);
            $obj    = new Client($config);
            $this->assertInternalType('object', $obj);
            $socket = $obj->getSocket();
            $this->assertInternalType('resource', $socket);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

    public function test__construct3()
    {
        try {
            $obj = new Client([
                'user' => getenv('ROS_USER'),
                'pass' => getenv('ROS_PASS'),
                'host' => getenv('ROS_HOST')
            ]);
            $this->assertInternalType('object', $obj);
            $socket = $obj->getSocket();
            $this->assertInternalType('resource', $socket);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

    public function test__constructEx()
    {
        $this->expectException(ConfigException::class);

        $obj = new Client([
            'user' => getenv('ROS_USER'),
            'pass' => getenv('ROS_PASS'),
        ]);
    }

    public function test__constructLegacy()
    {
        try {
            $config = new Config();
            $config->set('user', getenv('ROS_USER'))->set('pass', getenv('ROS_PASS'))
                ->set('host', getenv('ROS_HOST'))->set('port', (int) getenv('ROS_PORT_MODERN'))->set('legacy', true);
            $obj = new Client($config);
            $this->assertInternalType('object', $obj);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

    /**
     * Test non legacy connection on legacy router (pre 6.43)
     *
     * login() method recognise legacy router response and swap to legacy mode
     */
    public function test__constructLegacy2()
    {
        try {
            $config = new Config();
            $config->set('user', getenv('ROS_USER'))->set('pass', getenv('ROS_PASS'))
                ->set('host', getenv('ROS_HOST'))->set('port', (int) getenv('ROS_PORT_MODERN'))->set('legacy', false);
            $obj = new Client($config);
            $this->assertInternalType('object', $obj);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }


    public function test__constructWrongPass()
    {
        $this->expectException(ClientException::class);

        $config = (new Config())->set('attempts', 2);
        $config->set('user', getenv('ROS_USER'))->set('pass', 'admin2')->set('host', getenv('ROS_HOST'));
        $obj = new Client($config);
    }

    /**
     * @expectedException ClientException
     */
    public function test__constructWrongNet()
    {
        $this->expectException(ClientException::class);

        $config = new Config();
        $config->set('user', getenv('ROS_USER'))->set('pass', getenv('ROS_PASS'))->set('host', getenv('ROS_HOST'))->set('port', 11111);
        $obj = new Client($config);
    }

    public function testWriteRead()
    {
        $config = new Config();
        $config->set('user', getenv('ROS_USER'))->set('pass', getenv('ROS_PASS'))->set('host', getenv('ROS_HOST'));
        $obj = new Client($config);

        $query   = new Query('/ip/address/print');
        $readRaw = $obj->write($query)->read(false);
        $this->assertCount(10, $readRaw);
        $this->assertEquals('=.id=*1', $readRaw[1]);

        $query   = new Query('/ip/address/print');
        $readRaw = $obj->w($query)->read(false);
        $this->assertCount(10, $readRaw);
        $this->assertEquals('=.id=*1', $readRaw[1]);

        $query = new Query('/interface/getall');
        $read  = $obj->write($query)->r();
        $this->assertCount(1, $read);
        $this->assertEquals('*1', $read[0]['.id']);

        $query    = new Query('/interface');
        $readTrap = $obj->w($query)->r(false);
        $this->assertCount(3, $readTrap);
        $this->assertEquals('!trap', $readTrap[0]);

        $query    = new Query('/interface');
        $readTrap = $obj->wr($query, false);
        $this->assertCount(3, $readTrap);
        $this->assertEquals('!trap', $readTrap[0]);
    }

    public function testWriteReadString()
    {
        $config = new Config();
        $config->set('user', getenv('ROS_USER'))->set('pass', getenv('ROS_PASS'))->set('host', getenv('ROS_HOST'));
        $obj = new Client($config);

        $readTrap = $obj->wr('/interface', false);
        $this->assertCount(3, $readTrap);
        $this->assertEquals('!trap', $readTrap[0]);
    }

    public function testWriteReadArray()
    {
        $config = new Config();
        $config->set('user', getenv('ROS_USER'))->set('pass', getenv('ROS_PASS'))->set('host', getenv('ROS_HOST'));
        $obj = new Client($config);

        $readTrap = $obj->wr(['/interface'], false);
        $this->assertCount(3, $readTrap);
        $this->assertEquals('!trap', $readTrap[0]);
    }

    public function testFatal()
    {
        $config = new Config();
        $config->set('user', getenv('ROS_USER'))->set('pass', getenv('ROS_PASS'))->set('host', getenv('ROS_HOST'));
        $obj = new Client($config);

        $readTrap = $obj->wr('/quit');
        $this->assertCount(2, $readTrap);
        $this->assertEquals('!fatal', $readTrap[0]);
    }

    public function testWriteEx()
    {
        $this->expectException(QueryException::class);

        $config = new Config();
        $config->set('user', getenv('ROS_USER'))->set('pass', getenv('ROS_PASS'))->set('host', getenv('ROS_HOST'));
        $obj   = new Client($config);
        $error = $obj->write($obj)->read(false);
    }

    public function testGetConfig()
    {
        $obj = new Client([
            'user' => getenv('ROS_USER'),
            'pass' => getenv('ROS_PASS'),
            'host' => getenv('ROS_HOST')
        ]);

        $config = $obj->getConfig();
        $this->assertEquals('admin', $config->get('user'));
    }
}
