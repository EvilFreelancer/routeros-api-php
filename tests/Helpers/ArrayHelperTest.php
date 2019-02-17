<?php

namespace RouterOS\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use RouterOS\Helpers\ArrayHelper;

class ArrayHelperTest extends TestCase
{
    public function testCheckIfKeyNotExist()
    {
        $test1 = ArrayHelper::checkIfKeyNotExist(1, [0 => 'a', 1 => 'b', 2 => 'c']);
        $this->assertFalse($test1);

        $test2 = ArrayHelper::checkIfKeyNotExist('a', [1 => 'a', 2 => 'b', 3 => 'c']);
        $this->assertTrue($test2);
    }

    public function testCheckIfKeysNotExist()
    {
        $test1 = ArrayHelper::checkIfKeysNotExist([1, 2], [0 => 'a', 1 => 'b', 2 => 'c']);
        $this->assertTrue($test1);

        $test2 = ArrayHelper::checkIfKeysNotExist([3, 4], [0 => 'a', 1 => 'b', 2 => 'c']);
        $this->assertNotTrue($test2);
    }
}
