<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;

class QueryTest extends TestCase
{
    public function testConstruct(): void
    {
        try {
            $obj = new Query('test');
            $this->assertIsObject($obj);
        } catch (\Exception $e) {
            $this->assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    public function testConstructArr(): void
    {
        try {
            $obj = new Query('test', ['line1', 'line2', 'line3']);
            $this->assertIsObject($obj);
        } catch (\Exception $e) {
            $this->assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    public function testConstructArr2(): void
    {
        try {
            $obj = new Query(['test', 'line1', 'line2', 'line3']);
            $this->assertIsObject($obj);
        } catch (\Exception $e) {
            $this->assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    public function testGetEndpoint(): void
    {
        $obj  = new Query('test');
        $test = $obj->getEndpoint();
        $this->assertEquals('test', $test);
    }

    public function testGetEndpoint2(): void
    {
        $obj  = new Query(['zzz', 'line1', 'line2', 'line3']);
        $test = $obj->getEndpoint();
        $this->assertEquals('zzz', $test);
    }

    public function testGetEndpointEx(): void
    {
        $this->expectException(QueryException::class);

        $obj  = new Query(null);
        $test = $obj->getEndpoint();
    }

    public function testSetEndpoint(): void
    {
        $obj = new Query('test');
        $obj->setEndpoint('zzz');
        $test = $obj->getEndpoint();
        $this->assertEquals('zzz', $test);
    }

    public function testGetAttributes(): void
    {
        $obj  = new Query('test');
        $test = $obj->getAttributes();
        $this->assertCount(0, $test);
    }

    public function testSetAttributes(): void
    {
        $obj = new Query('test');
        $obj->setAttributes(['line1', 'line2', 'line3']);
        $test = $obj->getAttributes();
        $this->assertCount(3, $test);
    }

    public function testAdd(): void
    {
        $obj = new Query('test');
        $obj->add('line');

        $attrs = $obj->getAttributes();
        $this->assertCount(1, $attrs);
        $this->assertEquals($attrs[0], 'line');
    }

    public function testWhere(): void
    {
        $obj = new Query('test');
        $obj->where('key1', 'value1');
        $obj->where('key2', 'value2');

        $attrs = $obj->getAttributes();
        $this->assertCount(2, $attrs);
        $this->assertEquals($attrs[1], '?key2=value2');
    }


    public function testEqual(): void
    {
        $obj = new Query('test');
        $obj->equal('key1', 'value1');
        $obj->equal('key2', 'value2');

        $attrs = $obj->getAttributes();
        $this->assertCount(2, $attrs);
        $this->assertEquals($attrs[1], '=key2=value2');
    }

    public function testTag(): void
    {
        $obj = new Query('/test/test');
        $obj->where('key1', 'value1');
        $obj->tag('test');

        $query = $obj->getQuery();
        $this->assertCount(3, $query);
        $this->assertEquals($query[2], '.tag=test');
    }

    public function testOperator(): void
    {
        $obj = new Query('/test/test');
        $obj->where('key1', 'value1');
        $obj->operations('|');

        $query = $obj->getQuery();
        $this->assertCount(3, $query);
        $this->assertEquals($query[2], '?#|');
    }

    public function testWhereEx(): void
    {
        $this->expectException(QueryException::class);

        $obj = new Query('/richard/cheese');
        $obj->where('people', 'equals', 'shit');
    }

    public function testGetQuery(): void
    {
        $obj = new Query('test');
        $obj->add('line');

        $query = $obj->getQuery();
        $this->assertCount(2, $query);
        $this->assertEquals($query[0], 'test');
        $this->assertEquals($query[1], 'line');
    }

    public function testGetQueryEx(): void
    {
        $this->expectException(QueryException::class);

        $obj = new Query([null]);
        $obj->getQuery();
    }
}
