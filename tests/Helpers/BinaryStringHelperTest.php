<?php

namespace RouterOS\Tests\Helpers;

use PHPUnit\Framework\TestCase;

use RouterOS\Helpers\BinaryStringHelper;

/**
 * Limit code coverage to the class
 *
 * @coversDefaultClass \RouterOS\Helpers\BinaryStringHelper
 */
class BinaryStringHelperTest extends TestCase
{
    /**
     * @dataProvider IntegerToNBOBinaryStringProvider
     * @covers ::IntegerToNBOBinaryString
     *
     * @param $value
     * @param $expected
     */
    public function testIntegerToNBOBinaryString($value, $expected): void
    {
        $this->assertEquals($expected, BinaryStringHelper::IntegerToNBOBinaryString($value));
    }

    public function IntegerToNBOBinaryStringProvider(): array
    {
        $result = [
            [0, chr(0)], // lower boundary value
            [0xFFFFFFFF, chr(0xFF) . chr(0xFF) . chr(0xFF) . chr(0xFF)], // 32 bits maximal value

            // strange behaviour :
            //   TypeError: Argument 1 passed to RouterOS\Tests\Helpers\BinaryStringHelperTest::test__IntegerToNBOBinaryString() must be of the type integer, float given
            //   Seems that php auto convert to float 0xFFF.... 
            // 
            // [0xFFFFFFFFFFFFFFFF, chr(0xFF).chr(0xFF).chr(0xFF).chr(0xFF).chr(0xFF).chr(0xFF).chr(0xFF).chr(0xFF)],

            // Let's try random value
            [0x390DDD99, chr(0x39) . chr(0x0D) . chr(0xDD) . chr(0x99)],
        ];

        if (PHP_INT_SIZE > 4) {
            // -1 is encoded with 0xFFFFFFF.....
            // 64 bits maximal value (on a 64 bits system only)
            $result[] = [-1, chr(0xFF) . chr(0xFF) . chr(0xFF) . chr(0xFF) . chr(0xFF) . chr(0xFF) . chr(0xFF) . chr(0xFF)]; // 64 bits upper boundary value
        }

        return $result;
    }
}
