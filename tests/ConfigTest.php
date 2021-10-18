<?php

namespace RouterOS\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use RouterOS\Config;
use RouterOS\Exceptions\ConfigException;

class ConfigTest extends TestCase
{
    public function testConstruct(): void
    {
        try {
            $obj = new Config();
            $this->assertIsObject($obj);
        } catch (Exception $e) {
            $this->assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    public function testGetParameters(): void
    {
        $obj    = new Config();
        $params = $obj->getParameters();

        $this->assertCount(11, $params);
        $this->assertEquals(false, $params['legacy']);
        $this->assertEquals(false, $params['ssl']);
        $this->assertEquals(10, $params['timeout']);
        $this->assertEquals(10, $params['attempts']);
        $this->assertEquals(1, $params['delay']);
    }

    public function testGetParameters2(): void
    {
        $obj    = new Config(['timeout' => 100]);
        $params = $obj->getParameters();

        $this->assertCount(11, $params);
        $this->assertEquals(100, $params['timeout']);
    }

    public function testSet(): void
    {
        $obj = new Config();
        $obj->set('timeout', 111);
        $params = $obj->getParameters();

        $this->assertEquals(111, $params['timeout']);
    }

    public function testSetArr(): void
    {
        $obj    = new Config([
            'timeout' => 111,
        ]);
        $params = $obj->getParameters();

        $this->assertEquals(111, $params['timeout']);
    }

    public function testDelete(): void
    {
        $obj = new Config();
        $obj->delete('timeout');
        $params = $obj->getParameters();

        $this->assertArrayNotHasKey('timeout', $params);
    }

    public function testDeleteEx(): void
    {
        $this->expectException(ConfigException::class);

        $obj = new Config();
        $obj->delete('wrong');
    }

    public function testSetExceptionWrongType(): void
    {
        $this->expectException(ConfigException::class);

        $obj = new Config();
        $obj->set('delay', 'some string');
    }

    public function testSetExceptionWrongKey(): void
    {
        $this->expectException(ConfigException::class);

        $obj = new Config();
        $obj->set('wrong', 'some string');
    }

    public function testGet(): void
    {
        $obj   = new Config();
        $test1 = $obj->get('legacy');
        $this->assertEquals(false, $test1);

        $test2 = $obj->get('port');
        $this->assertEquals(8728, $test2);

        $obj->set('port', 10000);
        $test3 = $obj->get('port');
        $this->assertEquals(10000, $test3);

        $obj->delete('port');
        $obj->set('ssl', true);
        $test3 = $obj->get('port');
        $this->assertEquals(8729, $test3);
    }

    public function testGetEx(): void
    {
        $this->expectException(ConfigException::class);

        $obj = new Config();
        $obj->get('wrong');
    }
}
