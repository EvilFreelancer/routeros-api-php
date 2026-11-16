<?php

namespace RouterOS\Tests;

use PHPUnit\Framework\TestCase;
use RouterOS\APIConnector;
use RouterOS\Client;
use RouterOS\Interfaces\QueryInterface;
use RouterOS\Query;

class ClientCommunicationTest extends TestCase
{
    public function testReadParsesRowsFromConnector(): void
    {
        $connector = $this->createMock(APIConnector::class);
        $connector->method('readWord')->willReturnOnConsecutiveCalls(
            '!re', '=name=first', '',
            '!re', '=name=second', '',
            '!done', ''
        );
        $client = $this->client($connector);

        self::assertSame(
            [['name' => 'first'], ['name' => 'second']],
            $client->query('/system/resource/print')->read()
        );
    }

    public function testQueryAcceptsQueryInterface(): void
    {
        $connector = $this->createMock(APIConnector::class);
        $connector->expects(self::exactly(2))
            ->method('writeWord')
            ->withConsecutive(['/system/resource/print'], ['']);
        $client = $this->client($connector);

        $query = $this->createMock(QueryInterface::class);
        $query->method('getQuery')->willReturn(['/system/resource/print']);

        self::assertSame($client, $client->query($query));
    }

    public function testQueryDoesNotTrimAttributeValues(): void
    {
        $connector = $this->createMock(APIConnector::class);
        $connector->expects(self::exactly(3))
            ->method('writeWord')
            ->withConsecutive(
                ['/system/script/add'],
                ['=source=  leading and trailing  '],
                ['']
            );
        $client = $this->client($connector);
        $query = (new Query('/system/script/add'))->equal('source', '  leading and trailing  ');

        $client->query($query);
    }

    private function client(APIConnector $connector): Client
    {
        $client = new Client([
            'host' => '127.0.0.1',
            'user' => 'admin',
            'pass' => 'admin',
        ], false);

        $property = new \ReflectionProperty(Client::class, 'connector');
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }
        $property->setValue($client, $connector);

        return $client;
    }
}
