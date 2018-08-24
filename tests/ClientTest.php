<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use RouterOS\Client;
use RouterOS\Query;
use RouterOS\Config;
use RouterOS\Exceptions\Exception;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;

class ClientTest extends TestCase
{

    public function test__construct()
    {
        try {
            $config = new Config();
            $config->set('user', 'admin')->set('pass', 'admin')->set('host', '127.0.0.1');
            $obj = new Client($config);
            $this->assertInternalType('object', $obj);
            $socket = $obj->getSocket();
            $this->assertInternalType('resource', $socket);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

//    public function test__constructLegacy()
//    {
//        try {
//            $config = new Config();
//            $config->set('user', 'admin')->set('pass', 'admin')
//                ->set('host', '127.0.0.1')->set('port', 18728)->set('legacy', true);
//            $obj = new Client($config);
//            $this->assertInternalType('object', $obj);
//        } catch (\Exception $e) {
//            $this->assertContains('Must be initialized ', $e->getMessage());
//        }
//    }

    public function test__constructWrongPass()
    {
        $this->expectException(ClientException::class);

        $config = (new Config())->set('attempts', 2);
        $config->set('user', 'admin')->set('pass', 'admin2')->set('host', '127.0.0.1');
        $obj = new Client($config);
    }

    /**
     * @expectedException ClientException
     */
    public function test__constructWrongNet()
    {
        $this->expectException(ClientException::class);

        $config = new Config();
        $config->set('user', 'admin')->set('pass', 'admin')->set('host', '127.0.0.1')->set('port', 11111);
        $obj = new Client($config);
    }

    public function testWriteRead()
    {
        $config = new Config();
        $config->set('user', 'admin')->set('pass', 'admin')->set('host', '127.0.0.1');
        $obj = new Client($config);

        $query = new Query('/ip/address/print');
        $readRaw = $obj->write($query)->read(false);
        $this->assertCount(10, $readRaw);
        $this->assertEquals('=.id=*1', $readRaw[1]);

        $query = new Query('/interface/getall');
        $read = $obj->write($query)->read();
        $this->assertCount(1, $read);
        $this->assertEquals('*1', $read[0]['.id']);

        $query = new Query('/interface');
        $readTrap = $obj->write($query)->read();
        $this->assertCount(3, $readTrap);
        $this->assertEquals('!trap', $readTrap[0]);
    }

}
