<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use RouterOS\APIConnector;
use RouterOS\Streams\StringStream;
use RouterOS\Streams\ResourceStream;
use RouterOS\APILengthCoDec;
use RouterOS\Interfaces\StreamInterface;

/**
 * Limit code coverage to the class RouterOS\APIStream
 *
 * @coversDefaultClass \RouterOS\APIConnector
 */
class APIConnectorTest extends TestCase
{
    /**
     * Test that constructor is OK with different kinds of resources
     *
     * @covers ::__construct
     * @dataProvider constructProvider
     *
     * @param StreamInterface $stream        Cannot typehint, PHP refuse it
     * @param bool            $closeResource shall we close the resource ?
     */
    public function testConstruct(StreamInterface $stream, bool $closeResource = false): void
    {
        $apiStream = new APIConnector($stream);
        $this->assertInstanceOf(APIConnector::class, $apiStream);
        if ($closeResource) {
            $apiStream->close();
        }
    }

    public function constructProvider(): array
    {
        return [
            [new ResourceStream(fopen(__FILE__, 'rb')),], // Myself, sure I exists
            [new ResourceStream(fsockopen('tcp://' . getenv('ROS_HOST'), getenv('ROS_PORT_MODERN')))], // Socket
            [new ResourceStream(STDIN), false], // Try it, but do not close STDIN please !!!
            [new StringStream('Hello World !!!')], // Try it, but do not close STDIN please !!!
            [new StringStream('')], // Try it, but do not close STDIN please !!!
            // What else ?
        ];
    }

    /**
     * @covers ::readWord
     * @dataProvider readWordProvider
     *
     * @param APIConnector $connector
     * @param string       $expected
     */
    public function testReadWord(APIConnector $connector, string $expected): void
    {
        $this->assertSame($expected, $connector->readWord());
    }

    public function readWordProvider(): array
    {
        $longString = '=comment=' . str_repeat('a', 10000);
        $length     = strlen($longString);

        return [
            [new APIConnector(new StringStream(chr(0))), ''],
            [new APIConnector(new StringStream(chr(3) . '!re')), '!re'],
            [new APIConnector(new StringStream(chr(5) . '!done')), '!done'],
            [new APIConnector(new StringStream(APILengthCoDec::encodeLength($length) . $longString)), $longString],
        ];
    }

    /**
     * @covers ::writeWord
     * @dataProvider writeWordProvider
     *
     * @param APIConnector $connector
     * @param string       $toWrite
     * @param int          $expected
     */
    public function testWriteWord(APIConnector $connector, string $toWrite, int $expected): void
    {
        $this->assertEquals($expected, $connector->writeWord($toWrite));
    }

    public function writeWordProvider(): array
    {
        return [
            [new APIConnector(new StringStream('Have FUN !!!')), '', 1], // length is 0, but have to write it on 1 byte, minimum
            [new APIConnector(new StringStream('Have FUN !!!')), str_repeat(' ', 54), 55],  // arbitrary value
            [new APIConnector(new StringStream('Have FUN !!!')), str_repeat(' ', 127), 128], // maximum value for 1 byte encoding lentgth
            [new APIConnector(new StringStream('Have FUN !!!')), str_repeat(' ', 128), 130], // minimum value for 2 bytes encoding lentgth
            [new APIConnector(new StringStream('Have FUN !!!')), str_repeat(' ', 254), 256], // special value isn't it ?
            [new APIConnector(new StringStream('Have FUN !!!')), str_repeat(' ', 255), 257], // special value isn't it ?
        ];
    }
}
