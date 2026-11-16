<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use RouterOS\Client;
use RouterOS\ResponseIterator;

class ResponseIteratorTest extends TestCase
{
    public function testIteratesAndCachesRows(): void
    {
        $iterator = $this->iterator([
            '!re', '=.id=*1', '=name=first',
            '!re', '=.id=*2', '=name=second',
            '!done',
        ]);

        self::assertCount(2, $iterator);
        self::assertSame(0, $iterator->key());
        self::assertSame(['.id' => '*1', 'name' => 'first'], $iterator->current());

        $iterator->next();
        self::assertSame(['.id' => '*2', 'name' => 'second'], $iterator[1]);
        $iterator->prev();
        self::assertTrue($iterator->valid());
        self::assertTrue(isset($iterator[0]));

        $iterator[0] = ['name' => 'cached'];
        self::assertSame(['name' => 'cached'], $iterator->current());

        unset($iterator[1]);
        self::assertFalse(isset($iterator[1]));
        self::assertCount(1, $iterator);

        $iterator->rewind();
        self::assertSame(0, $iterator->key());
    }

    /**
     * @dataProvider emptyResponseProvider
     */
    public function testResponseWithoutRowsIsEmpty(array $raw): void
    {
        $iterator = $this->iterator($raw);

        self::assertCount(0, $iterator);
        self::assertFalse($iterator->valid());
        self::assertNull($iterator->current());
        self::assertFalse($iterator[0]);
    }

    public function emptyResponseProvider(): array
    {
        return [
            [['!done']],
            [['!done', '=ret=123']],
            [['!trap', '=message=failure', '!done']],
        ];
    }

    public function testRowWithoutAttributesHasNoValue(): void
    {
        $iterator = $this->iterator(['!re', '!done']);

        self::assertNull($iterator->current());
        self::assertFalse($iterator[0]);
    }

    public function testNativeSerializationRoundTripKeepsRows(): void
    {
        $iterator = $this->iterator(['!re', '=name=first', '!done']);

        $restored = unserialize(serialize($iterator));

        self::assertSame(['name' => 'first'], $restored[0]);
    }

    private function iterator(array $raw): ResponseIterator
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['read'])
            ->getMock();
        $client->method('read')->willReturn($raw);

        return new ResponseIterator($client);
    }
}
