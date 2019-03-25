<?php

namespace RouterOS\Tests\Streams;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsType;

use RouterOS\Streams\StringStream;
use RouterOS\Exceptions\StreamException;

/**
 * Limit code coverage to the class RouterOS\APIStream
 *
 * @coversDefaultClass \RouterOS\Streams\StringStream
 */
class StringStreamTest extends TestCase
{
    /**
     * @covers ::__construct
     * @dataProvider constructProvider
     *
     * @param   string $string
     */
    public function test__construct(string $string)
    {
        $this->assertInstanceOf(StringStream::class, new StringStream($string));
    }

    public function constructProvider(): array
    {
        return [
            [chr(0)],
            [''],
            ['1'],
            ['lkjl' . chr(0) . 'kjkljllkjkljljklkjkljlkjljlkjkljkljlkjjll'],
        ];
    }


    /**
     * Test that write function returns the effective written bytes
     *
     * @covers ::write
     * @dataProvider writeProvider
     *
     * @param   string   $string   the string to write
     * @param   int|null $length   the count if bytes to write
     * @param   int      $expected the number of bytes that must be writen
     */

    public function test__write(string $string, $length, int $expected)
    {
        $stream = new StringStream('Does not matters');
        if (null === $length) {
            $this->assertEquals($expected, $stream->write($string));
        } else {
            $this->assertEquals($expected, $stream->write($string, $length));
        }

    }

    public function writeProvider(): array
    {
        return [
            ['', 0, 0],
            ['', 10, 0],
            ['', null, 0],
            ['Yabala', 0, 0],
            ['Yabala', 1, 1],
            ['Yabala', 6, 6],
            ['Yabala', 100, 6],
            ['Yabala', null, 6],
            [chr(0), 0, 0],
            [chr(0), 1, 1],
            [chr(0), 100, 1],
            [chr(0), null, 1],
        ];
    }

    /**
     * @covers ::write
     * @expectedException \InvalidArgumentException
     */
    public function test__writeWithNegativeLength()
    {
        $stream = new StringStream('Does not matters');
        $stream->write('PLOP', -1);
    }

    /**
     * Test read function
     *
     * @throws \RouterOS\Exceptions\StreamException
     */
    public function test__read()
    {
        $stream = new StringStream('123456789');

        $this->assertEquals('', $stream->read(0));
        $this->assertEquals('1', $stream->read(1));
        $this->assertEquals('23', $stream->read(2));
        $this->assertEquals('456', $stream->read(3));
        $this->assertEquals('', $stream->read(0));
        $this->assertEquals('789', $stream->read(4));
    }

    /**
     * @expectedException \InvalidArgumentException
     *
     * @throws \RouterOS\Exceptions\StreamException
     */
    public function test__readBadLength()
    {
        $stream = new StringStream('123456789');
        $stream->read(-1);
    }

    /**
     * @covers ::read
     * @dataProvider readWhileEmptyProvider
     * @expectedException \RouterOS\Exceptions\StreamException
     *
     * @param   StringStream $stream
     * @param   int          $length
     * @throws  \RouterOS\Exceptions\StreamException
     */
    public function test__readWhileEmpty(StringStream $stream, int $length)
    {
        $stream->read($length);
    }

    /**
     * @return \Generator
     * @throws StreamException
     */
    public function readWhileEmptyProvider()
    {
        $stream = new StringStream('123456789');
        $stream->read(9);
        yield [$stream, 1];

        $stream = new StringStream('123456789');
        $stream->read(5);
        $stream->read(4);
        yield [$stream, 1];

        $stream = new StringStream('');
        yield [$stream, 1];
    }

    /**
     * @expectedException \RouterOS\Exceptions\StreamException
     */
    public function testReadClosed()
    {
        $stream = new StringStream('123456789');
        $stream->close();
        $stream->read(1);
    }
}
