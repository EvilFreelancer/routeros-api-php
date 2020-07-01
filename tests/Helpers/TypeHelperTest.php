<?php

namespace RouterOS\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use RouterOS\Helpers\TypeHelper;

class TypeHelperTest extends TestCase
{
    public function testCheckIfTypeMismatch(): void
    {
        $test1 = TypeHelper::checkIfTypeMismatch(gettype(true), gettype(false));
        $this->assertFalse($test1);

        $test2 = TypeHelper::checkIfTypeMismatch(gettype(true), gettype('false'));
        $this->assertTrue($test2);
    }
}
