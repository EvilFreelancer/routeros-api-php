<?php

namespace RouterOS\Tests\Streams;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsType;

use RouterOS\Streams\ResourceStream;
use RouterOS\Exceptions\StreamException;

/**
 * Limit code coverage to the class RouterOS\APIStream
 * @coversDefaultClass RouterOS\Streams\ResourceStream
 */
class ResourceStreamTest extends TestCase
{
    /**
     * Test that constructor throws an InvalidArgumentException on bad parameter type
     * 
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     * @dataProvider constructNotResourceProvider
     */

    public function test__constructNotResource($notResource)
    {
        new ResourceStream($notResource);
    }

    /**
     * Data provider for test__constructNotResource
     *
     * returns data not of type resource
     */
    public function constructNotResourceProvider()
    {
        return [
            [0],          // integer
            [3.14],       // float
            ['a string'], // string
            [
                [ 0 , 3.14 ]   // Array
            ],
            [ new \stdClass() ], // Object
            // What else ?
        ];
    }

    /**
     * Test that constructor is OK with different kinds of resources
     * 
     * @covers ::__construct
     * @dataProvider constructProvider
     * @param resource $resource Cannot typehint, PHP refuse it
     * @param bool $closeResource shall we close the resource ?
     */
    public function test_construct($resource, bool $closeResource=true)
    {
        $resourceStream = new ResourceStream($resource);

        $stream = $this->getObjectAttribute($resourceStream, 'stream');
        $this->assertInternalType(IsType::TYPE_RESOURCE, $stream);

        if ($closeResource)
        {
            fclose($resource);
        }
    }

    /**
     * Data provider for test__construct
     *
     * returns data of type resource
     */
    public function constructProvider()
    {
        return [
            [ fopen(__FILE__, 'r'), ], // Myself, sure I exists
            [ fsockopen('tcp://127.0.0.1', 18728),  ], // Socket 
            [ STDIN, false ], // Try it, but do not close STDIN please !!!
            // What else ?
        ];
    }

    /**
     * Test that read function return expected values, and that consecutive reads return data
     * 
     * @covers ::read
     * @dataProvider readProvider
     * @param resource $resource Cannot typehint, PHP refuse it
     * @param string $expected the rsult we should have 
     */
    public function test__read(ResourceStream $stream, string $expected)
    {
        $this->assertSame($expected, $stream->read(strlen($expected)));
    }

    public function readProvider()
    {
        $resource = fopen(__FILE__, 'r');
        $me = new ResourceStream($resource);
        return [
            [ $me, '<'],  // Read for byte 
            [ $me, '?php'], // Read following bytes. File statrts with "<php"
        ];
        fclose($resource);
    }

    /**
     * Test that read invalid lengths
     * 
     * @covers ::read
     * @dataProvider readBadLengthProvider
     * @expectedException \InvalidArgumentException
     * @param resource $resource Cannot typehint, PHP refuse it
     */
    public function test__readBadLength(ResourceStream $stream, int $length)
    {
        $stream->read($length);
    }

    public function readBadLengthProvider()
    {
        $resource = fopen(__FILE__, 'r');
        $me = new ResourceStream($resource);
        return [
            [ $me, 0 ],
            [ $me, -1 ],
        ];
        fclose($resource);
    }
    /**
     * Test read to invalid resource
     * 
     * @covers ::read
     * @dataProvider readBadResourceProvider
     * @expectedException RouterOS\Exceptions\StreamException
     * @param resource $resource Cannot typehint, PHP refuse it
     */
    public function test__readBadResource(ResourceStream $stream, int $length)
    {
        $stream->read($length);
    }

    public function readBadResourceProvider()
    {
        $resource = fopen(__FILE__, 'r');
        $me = new ResourceStream($resource);
        fclose($resource);
        return [
            [ $me, 1 ],
        ];
    }

    /**
     * Test that write function returns writen length
     * 
     * @covers ::write
     * @dataProvider writeProvider
     * @param ResourceStram $resource to test
     * @param string $toWrite the writed string 
     */
    public function test__write(ResourceStream $stream, string $toWrite)
    {
        $this->assertEquals(strlen($toWrite) , $stream->write($toWrite));
    }

    public function writeProvider()
    {
        $resource = fopen("/dev/null", 'w');
        $null = new ResourceStream($resource);
        return [
            [ $null, 'yyaagagagag'],  // Take that 
        ];
        fclose($resource);
    }

    /**
     * Test write to invalid resource
     * 
     * @covers ::write
     * @dataProvider writeBadResourceProvider
     * @expectedException RouterOS\Exceptions\StreamException
     * @param resource $resource to test
     * @param string $toWrite the writed string 
     */
    public function test__writeBadResource(ResourceStream $stream, string $toWrite)
    {
        $stream->write($toWrite);
    }

    public function writeBadResourceProvider()
    {
        $resource = fopen('/dev/null', 'w');
        $me = new ResourceStream($resource);
        fclose($resource);
        return [
            [ $me, 'sasasaas' ],  // Take that
        ];
    }

    /**
     * Test double close resource
     * 
     * @covers ::close
     * @dataProvider doubleCloseProvider
     * @expectedException RouterOS\Exceptions\StreamException
     * @param resource $resource to test
     */
    public function test_doubleClose(ResourceStream $stream)
    {
        $stream->close();
        $stream->close();
    }

    public function doubleCloseProvider()
    {
        return [
            [ new ResourceStream(fopen('/dev/null', 'w')), 'sasasaas' ],  // Take that
        ];
    }

    /**
     * Test write to closed resource
     * 
     * @covers ::close
     * @covers ::write
     * @dataProvider writeClosedResourceProvider
     * @expectedException RouterOS\Exceptions\StreamException
     * @param resource $resource to test
     * @param string $toWrite the writed string 
     */
    public function test_close(ResourceStream $stream, string $toWrite)
    {
        $stream->close();
        $stream->write($toWrite);
    }

    public function writeClosedResourceProvider()
    {
        return [
            [ new ResourceStream(fopen('/dev/null', 'w')), 'sasasaas' ],  // Take that
        ];
    }

}