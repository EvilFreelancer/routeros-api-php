<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use RouterOS\Query;

class QueryTest extends TestCase
{

    public function test__construct()
    {
        try {
            $obj = new Query('test');
            $this->assertInternalType('object', $obj);
        } catch (\Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

    public function testGetEndpoint()
    {
        $obj = new Query('test');
        $test = $obj->getEndpoint();
        $this->assertEquals($test, 'test');
    }

    public function testGetAttributes()
    {
        $obj = new Query('test');
        $test = $obj->getAttributes();
        $this->assertCount(0, $test);
    }

    public function testAdd()
    {
        $obj = new Query('test');
        $obj->add('line');

        $attrs = $obj->getAttributes();
        $this->assertCount(1, $attrs);
        $this->assertEquals($attrs[0], 'line');
    }

    public function testGetQuery()
    {
        $obj = new Query('test');
        $obj->add('line');

        $query = $obj->getQuery();
        $this->assertCount(2, $query);
        $this->assertEquals($query[0], 'test');
        $this->assertEquals($query[1], 'line');
    }

}
