<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsType;

use RouterOS\APILengthCoDec;
use RouterOS\Streams\StringStream;
use RouterOS\Helpers\BinaryStringHelper;

/**
 * Limit code coverage to the class
 *
 * @coversDefaultClass \RouterOS\APILengthCoDec
 */
class APILengthCoDecTest extends TestCase
{
    /**
     * @dataProvider encodeLengthNegativeProvider
     * @expectedException \DomainException
     * @covers ::encodeLength
     */
    public function test__encodeLengthNegative($length)
    {
        APILengthCoDec::encodeLength($length);
    }

    public function encodeLengthNegativeProvider(): array
    {
        return [
            [-1],
            [PHP_INT_MIN],
        ];
    }

    /**
     * @dataProvider encodedLengthProvider
     * @covers ::encodeLength
     */
    public function test__encodeLength($expected, $length)
    {
        $this->assertEquals(BinaryStringHelper::IntegerToNBOBinaryString((int) $expected), APILengthCoDec::encodeLength($length));
    }

    public function encodedLengthProvider(): array
    {
        // [encoded length value, length value] 
        $result = [
            [0, 0],        // Low limit value for 1 byte encoded length
            [0x39, 0x39],  // Arbitrary median value for 1 byte encoded length
            [0x7f, 0x7F],  // High limit value for 1 byte encoded length

            [0x8080, 0x80],   // Low limit value for 2 bytes encoded length
            [0x9C42, 0x1C42], // Arbitrary median value for 2 bytes encoded length
            [0xBFFF, 0x3FFF], // High limit value for 2 bytes encoded length

            [0xC04000, 0x4000],   // Low limit value for 3 bytes
            [0xCAD73B, 0xAD73B],  // Arbitrary median value for 3 bytes encoded length
            [0xDFFFFF, 0x1FFFFF], // High limit value for 3 bytes encoded length

            [0xE0200000, 0x200000],   // Low limit value for 4 bytes encoded length
            [0xE5AD736B, 0x5AD736B],  // Arbitrary median value for 4 bytes encoded length
            [0xEFFFFFFF, 0xFFFFFFF],  // High limit value for 4 bytes encoded length
        ];

        if (PHP_INT_SIZE > 4) {
            $result[] = [0xF010000000, 0x10000000];  // Low limit value for 5 bytes encoded length
            $result[] = [0xF10D4EF9C3, 0x10D4EF9C3]; // Arbitrary median value for 5 bytes encoded length
            $result[] = [0xF7FFFFFFFF, 0x7FFFFFFFF]; // High limit value for 5 bytes encoded length
        }

        return $result;
    }

    /**
     * @dataProvider encodedLengthProvider
     * @covers ::decodeLength
     */
    public function test__decodeLength($encodedLength, $expected)
    {
        // We have to provide $encodedLength as a "bytes" stream
        $stream = new StringStream(BinaryStringHelper::IntegerToNBOBinaryString($encodedLength));
        $this->assertEquals($expected, APILengthCoDec::decodeLength($stream));
    }

    /**
     * @dataProvider decodeLengthControlWordProvider
     * @covers ::decodeLength
     * @expectedException \UnexpectedValueException
     */
    public function test_decodeLengthControlWord(string $encodedLength)
    {
        APILengthCoDec::decodeLength(new StringStream($encodedLength));
    }

    public function decodeLengthControlWordProvider(): array
    {
        // Control bytes: 5 most significance its sets to 1
        return [
            [chr(0xF8)], // minimum
            [chr(0xFC)], // arbitrary value
            [chr(0xFF)], // maximum
        ];
    }
}
