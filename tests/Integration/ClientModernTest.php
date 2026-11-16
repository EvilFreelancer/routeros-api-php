<?php

namespace RouterOS\Tests\Integration;

use PHPUnit\Framework\TestCase;
use RouterOS\Client;
use RouterOS\Query;

class ClientModernTest extends TestCase
{
    public function testMultilineScriptSourceAndDetailAttribute(): void
    {
        $client = new Client([
            'host' => getenv('ROS_HOST_MODERN'),
            'port' => (int) getenv('ROS_PORT_MODERN'),
            'user' => getenv('ROS_USER'),
            'pass' => getenv('ROS_PASS'),
        ]);
        $name = 'routeros-api-php-multiline';
        $source = ":local first \"one\";\n:local second \"two\";\r\n:put \"\$first\$second\";";
        $id = null;

        try {
            $client->query(
                (new Query('/system/script/add'))
                    ->equal('name', $name)
                    ->equal('source', $source)
            )->read();

            $response = $client->query(
                (new Query('/system/script/print'))->where('name', '=', $name)
            )->read();

            self::assertCount(1, $response);
            self::assertSame($source, $response[0]['source']);
            $id = $response[0]['.id'];

            $run = $client->query(
                (new Query('/system/script/run'))->equal('number', $name)
            )->read();
            self::assertSame([], $run);

            $detailed = $client->query(new Query('/system/package/print', ['=detail=']))->read();
            self::assertNotEmpty($detailed);
            self::assertArrayHasKey('name', $detailed[0]);
        } finally {
            if (null !== $id) {
                $client->query(
                    (new Query('/system/script/remove'))->equal('.id', $id)
                )->read();
            }
        }
    }
}
