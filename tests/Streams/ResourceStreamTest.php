<?php

namespace RouterOS\Tests\Streams;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RouterOS\Exceptions\StreamException;
use RouterOS\Streams\ResourceStream;
use stdClass;

/**
 * Limit code coverage to the class RouterOS\APIStream
 *
 * @coversDefaultClass \RouterOS\Streams\ResourceStream
 */
class ResourceStreamTest extends TestCase
{
    /**
     * Test that constructor throws an InvalidArgumentException on bad parameter type
     *
     * @covers ::__construct
     * @dataProvider constructNotResourceProvider
     *
     * @param $notResource
     */
    public function testConstructNotResource($notResource): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ResourceStream($notResource);
    }

    /**
     * Data provider for test__constructNotResource
     *
     * returns data not of type resource
     */
    public function constructNotResourceProvider(): array
    {
        return [
            [0],          // integer
            [3.14],       // float
            ['a string'], // string
            [
                [0, 3.14],   // Array
            ],
            [new stdClass()], // Object
            // What else ?
        ];
    }

    /**
     * Test that constructor is OK with different kinds of resources
     *
     * @covers ::__construct
     * @dataProvider constructProvider
     *
     * @param resource $resource      Cannot typehint, PHP refuse it
     * @param bool     $closeResource shall we close the resource ?
     */
    public function testConstruct($resource, bool $closeResource = true): void
    {
        $resourceStream = new class($resource) extends ResourceStream {
            public function getStream()
            {
                return $this->stream;
            }
        };

        $stream = $resourceStream->getStream();
        $this->assertIsResource($stream);

        if ($closeResource) {
            fclose($resource);
        }
    }

    /**
     * Data provider for test__construct
     *
     * @return array data of type resource
     */
    public function constructProvider(): array
    {
        return [
            [fopen(__FILE__, 'rb')], // Myself, sure I exists
            [fsockopen('tcp://' . getenv('ROS_HOST_MODERN'), getenv('ROS_PORT_MODERN'))], // Socket
            [STDIN, false], // Try it, but do not close STDIN please !!!
            // What else ?
        ];
    }

    /**
     * Test that read function return expected values, and that consecutive reads return data
     *
     * @covers ::read
     * @dataProvider readProvider
     *
     * @param ResourceStream $stream   Cannot typehint, PHP refuse it
     * @param string         $expected the result we should have
     *
     * @throws \RouterOS\Exceptions\StreamException
     * @throws \InvalidArgumentException
     */
    public function testRead(ResourceStream $stream, string $expected): void
    {
        $this->assertSame($expected, $stream->read(strlen($expected)));
    }

    public function readProvider(): array
    {
        $resource = fopen(__FILE__, 'rb');
        $me       = new ResourceStream($resource);

        return [
            [$me, '<'],  // Read for byte
            [$me, '?php'], // Read following bytes. File statrts with "<php"
        ];
    }

    /**
     * Test that read invalid lengths
     *
     * @covers ::read
     * @dataProvider readBadLengthProvider
     *
     * @param ResourceStream $stream Cannot typehint, PHP refuse it
     * @param int            $length
     *
     * @throws  \RouterOS\Exceptions\StreamException
     * @throws  \InvalidArgumentException
     */
    public function testReadBadLength(ResourceStream $stream, int $length): void
    {
        $this->expectException(InvalidArgumentException::class);
        $stream->read($length);
    }

    public function readBadLengthProvider(): array
    {
        $resource = fopen(__FILE__, 'rb');
        $me       = new ResourceStream($resource);

        return [
            [$me, 0],
            [$me, -1],
        ];
    }

    /**
     * Test read to invalid resource
     *
     * @covers ::read
     * @throws \RouterOS\Exceptions\StreamException
     */
    public function testReadBadResource(): void
    {
        $this->expectException(StreamException::class);

        $resource = fopen(__DIR__, 'rb');
        $stream   = new ResourceStream($resource);
        fclose($resource);

        //dd($stream);

        $stream->read(1);
    }

    /**
     * Test that write function returns writen length
     *
     * @covers ::write
     * @dataProvider writeProvider
     *
     * @param ResourceStream $stream  to test
     * @param string         $toWrite the writed string
     *
     * @throws  \RouterOS\Exceptions\StreamException
     */
    public function testWrite(ResourceStream $stream, string $toWrite): void
    {
        $this->assertEquals(strlen($toWrite), $stream->write($toWrite));
    }

    public function writeProvider(): array
    {
        $resource = fopen('/dev/null', 'wb');
        $null     = new ResourceStream($resource);

        return [
            [$null, 'yyaagagagag'],  // Take that
        ];
    }

    /**
     * Test write to invalid resource
     *
     * @covers ::write
     * @dataProvider writeBadResourceProvider
     *
     * @param ResourceStream $stream  to test
     * @param string         $toWrite the written string
     */
    public function testWriteBadResource(ResourceStream $stream, string $toWrite): void
    {
        $this->expectException(StreamException::class);
        $stream->write($toWrite);
    }

    public function writeBadResourceProvider(): array
    {
        $resource = fopen('/dev/null', 'wb');
        $me       = new ResourceStream($resource);
        fclose($resource);

        return [
            [$me, 'sasasaas'],  // Take that
        ];
    }

    /**
     * Test double close resource
     *
     * @covers ::close
     * @dataProvider doubleCloseProvider
     *
     * @param ResourceStream $stream to test
     */
    public function testDoubleClose(ResourceStream $stream): void
    {
        $this->expectException(StreamException::class);
        $stream->close();
        $stream->close();
    }

    public function doubleCloseProvider(): array
    {
        return [
            [new ResourceStream(fopen('/dev/null', 'wb')), 'sasasaas'],  // Take that
        ];
    }

    /**
     * Test write to closed resource
     *
     * @covers ::close
     * @covers ::write
     * @dataProvider writeClosedResourceProvider
     *
     * @param ResourceStream $stream  to test
     * @param string         $toWrite the written string
     */
    public function testClose(ResourceStream $stream, string $toWrite)
    {
        $this->expectException(StreamException::class);
        $stream->close();
        $stream->write($toWrite);
    }

    public function writeClosedResourceProvider(): array
    {
        return [
            [new ResourceStream(fopen('/dev/null', 'wb')), 'sasasaas'],  // Take that
        ];
    }
}
