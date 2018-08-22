<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use RouterOS\Config;
use RouterOS\Exceptions\Exception;

class ConfigTest extends TestCase
{
    public function test__construct()
    {
        try {
            $obj = new Config();
            $this->assertInternalType('object', $obj);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

    public function testGetParameters()
    {
        $obj = new Config();
        $params = $obj->getParameters();

        $this->assertCount(5, $params);
        $this->assertEquals($params['legacy'], false);
        $this->assertEquals($params['ssl'], false);
        $this->assertEquals($params['timeout'], 10);
        $this->assertEquals($params['attempts'], 10);
        $this->assertEquals($params['delay'], 1);
    }

    public function testSet()
    {
        $obj = new Config();
        $obj->set('timeout', 111);
        $params = $obj->getParameters();

        $this->assertEquals($params['timeout'], 111);
    }

    public function testDelete()
    {
        $obj = new Config();
        $obj->delete('timeout');
        $params = $obj->getParameters();

        $this->assertArrayNotHasKey('timeout', $params);
    }

    public function testDeleteEx()
    {
        $this->expectException(Exception::class);

        $obj = new Config();
        $obj->delete('wrong');
    }

    /**
     * @throws Exception
     */
    public function testSetEx1()
    {
        $this->expectException(Exception::class);

        $obj = new Config();
        $obj->set('delay', 'some string');
    }

    public function testSetEx2()
    {
        $this->expectException(Exception::class);

        $obj = new Config();
        $obj->set('wrong', 'some string');
    }

    public function testGet()
    {
        $obj = new Config();
        $test1 = $obj->get('legacy');
        $this->assertEquals($test1, false);

        $test2 = $obj->get('port');
        $this->assertEquals($test2, 8728);

        $obj->set('port', 10000);
        $test3 = $obj->get('port');
        $this->assertEquals($test3, 10000);


        $obj->delete('port');
        $obj->set('ssl', true);
        $test3 = $obj->get('port');
        $this->assertEquals($test3, 8729);
    }

    public function testGetEx()
    {
        $this->expectException(Exception::class);

        $obj = new Config();
        $test = $obj->get('wrong');
    }
}
