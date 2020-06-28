<?php

namespace RouterOS\Tests\Laravel;

use RouterOS\Config;
use RouterOS\Laravel\Wrapper;

class ServiceProviderTests extends TestCase
{
    private $client = [
        "__construct",
        "write",
        "query",
        "read",
        "readAsIterator",
        "parseResponse",
        "connect",
        "export",
        "getSocket",
        "w",
        "q",
        "r",
        "ri",
        "wr",
        "qr",
        "wri",
        "qri",
    ];

    public function testAbstractsAreLoaded(): void
    {
        $manager = app(Wrapper::class);
        $this->assertInstanceOf(Wrapper::class, $manager);
    }

    public function testConfig(): void
    {
        $config = \RouterOS::config([
            'host' => '192.168.1.3',
            'user' => 'admin',
            'pass' => 'admin'
        ]);
        $this->assertInstanceOf(Config::class, $config);

        $params = $config->getParameters();
        $this->assertArrayHasKey('host', $params);
        $this->assertArrayHasKey('user', $params);
        $this->assertArrayHasKey('pass', $params);
        $this->assertArrayHasKey('ssl', $params);
        $this->assertArrayHasKey('legacy', $params);
        $this->assertArrayHasKey('timeout', $params);
        $this->assertArrayHasKey('attempts', $params);
        $this->assertArrayHasKey('delay', $params);
    }

    public function testClient(): void
    {
        $client = \RouterOS::client([
            'host' => '192.168.1.3',
            'user' => 'admin',
            'pass' => 'admin'
        ], false);

        $this->assertEquals(get_class_methods($client), $this->client);
    }
}
