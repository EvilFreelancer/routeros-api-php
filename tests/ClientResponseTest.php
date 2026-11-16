<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use RouterOS\Client;

class ClientResponseTest extends TestCase
{
    public function testParseResponsePreservesMultilineValues(): void
    {
        $client = new Client([
            'host' => '127.0.0.1',
            'user' => 'admin',
            'pass' => 'admin',
        ], false);

        $contents = "-----BEGIN CERTIFICATE-----\r\nfirst line\nsecond line\r\n-----END CERTIFICATE-----";

        self::assertSame(
            [['contents' => $contents]],
            $client->parseResponse(['!re', '=contents=' . $contents, '!done'])
        );
    }
}
