<?php

namespace RouterOS\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use RouterOS\Client;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;
use RouterOS\Config;
use RouterOS\Exceptions\ClientException;

class ClientTest extends TestCase
{
    /**
     * @var array
     */
    public $config;

    /**
     * @var \RouterOS\Client
     */
    public $client;

    /**
     * @var int
     */
    public $port_modern;

    /**
     * @var int
     */
    public $port_legacy;

    public function setUp(): void
    {
        $this->config = [
            'user'     => getenv('ROS_USER'),
            'pass'     => getenv('ROS_PASS'),
            'host'     => getenv('ROS_HOST'),
            'ssh_port' => (int) getenv('ROS_SSH_PORT'),
        ];

        $this->client = new Client($this->config);

        $this->port_modern = (int) getenv('ROS_PORT_MODERN');
        $this->port_legacy = (int) getenv('ROS_PORT_LEGACY');
    }

    public function testConstruct(): void
    {
        try {
            $config = new Config();
            $config
                ->set('user', $this->config['user'])
                ->set('pass', $this->config['pass'])
                ->set('host', $this->config['host']);

            $obj = new Client($config);
            $this->assertIsObject($obj);
            $socket = $obj->getSocket();
            $this->assertIsResource($socket);
        } catch (Exception $e) {
            $this->assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    public function testConstruct2(): void
    {
        try {
            $config = new Config($this->config);
            $obj    = new Client($config);
            $this->assertIsObject($obj);
            $socket = $obj->getSocket();
            $this->assertIsResource($socket);
        } catch (Exception $e) {
            $this->assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    public function testConstruct3(): void
    {
        try {
            $obj = new Client($this->config);
            $this->assertIsObject($obj);
            $socket = $obj->getSocket();
            $this->assertIsResource($socket);
        } catch (Exception $e) {
            $this->assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    public function testConstructException(): void
    {
        $this->expectException(ConfigException::class);

        new Client([
            'user' => $this->config['user'],
            'pass' => $this->config['pass'],
        ]);
    }

    public function testConstructExceptionBadHost(): void
    {
        $this->expectException(ClientException::class);

        new Client([
            'host'     => '127.0.0.1',
            'port'     => 123456,
            'attempts' => 0,
            'user'     => $this->config['user'],
            'pass'     => $this->config['pass'],
        ]);
    }

    public function testConstructLegacy(): void
    {
        try {
            $obj = new Client([
                'user'   => $this->config['user'],
                'pass'   => $this->config['pass'],
                'host'   => $this->config['host'],
                'port'   => $this->port_legacy,
                'legacy' => true
            ]);
            $this->assertIsObject($obj);
        } catch (Exception $e) {
            $this->assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    /**
     * Test non legacy connection on legacy router (pre 6.43)
     *
     * login() method recognise legacy router response and swap to legacy mode
     */
    public function testConstructLegacy2(): void
    {
        try {
            $obj = new Client([
                'user'   => $this->config['user'],
                'pass'   => $this->config['pass'],
                'host'   => $this->config['host'],
                'port'   => $this->port_legacy,
                'legacy' => false
            ]);
            $this->assertIsObject($obj);
        } catch (Exception $e) {
            $this->assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    public function testConstructWrongPass(): void
    {
        $this->expectException(ClientException::class);

        new Client([
            'user'     => $this->config['user'],
            'pass'     => 'admin2',
            'host'     => $this->config['host'],
            'attempts' => 2
        ]);
    }

    public function testConstructWrongNet(): void
    {
        $this->expectException(ClientException::class);

        new Client([
            'user'     => $this->config['user'],
            'pass'     => $this->config['pass'],
            'host'     => $this->config['host'],
            'port'     => 11111,
            'attempts' => 2
        ]);
    }

    public function testQueryRead(): void
    {
        /*
         * Build query with where
         */

        $read = $this->client->query('/system/package/print', ['name'])->read();
        $this->assertNotEmpty($read);

        $read = $this->client->query('/system/package/print', ['.id', '*1'])->read();
        $this->assertCount(1, $read);

        $read = $this->client->query('/system/package/print', ['.id', '=', '*1'])->read();
        $this->assertCount(1, $read);

        $read = $this->client->query('/system/package/print', [['name']])->read();
        $this->assertNotEmpty($read);

        $read = $this->client->query('/system/package/print', [['.id', '*1']])->read();
        $this->assertCount(1, $read);

        $read = $this->client->query('/system/package/print', [['.id', '=', '*1']])->read();
        $this->assertCount(1, $read);

        /*
         * Build query with operations
         */

        $read = $this->client->query('/interface/print', [
            ['type', 'ether'],
            ['type', 'vlan']
        ], '|')->read();
        $this->assertCount(1, $read);
        $this->assertEquals('*1', $read[0]['.id']);

        /*
         * Build query with tag
         */

        $read = $this->client->query('/system/package/print', null, null, 'zzzz')->read();

        // $this->assertCount(13, $read);
        $this->assertEquals('zzzz', $read[0]['tag']);
    }

    public function testReadAsIterator(): void
    {
        $result = $this->client->query('/system/package/print')->readAsIterator();
        $this->assertIsObject($result);
    }

    public function testWriteReadString(): void
    {
        $readTrap = $this->client->query('/interface')->read(false);
        $this->assertCount(3, $readTrap);
        $this->assertEquals('!trap', $readTrap[0]);
    }

    public function testFatal(): void
    {
        $readTrap = $this->client->query('/quit')->read();
        $this->assertCount(2, $readTrap);
        $this->assertEquals('!fatal', $readTrap[0]);
    }

    public function queryExceptionDataProvider(): array
    {
        return [
            // Wrong amount of parameters
            ['exception' => ClientException::class, 'endpoint' => '/quiet', 'attributes' => [[]]],
            ['exception' => ClientException::class, 'endpoint' => '/quiet', 'attributes' => [[], ['a', 'b', 'c']]],
            ['exception' => ClientException::class, 'endpoint' => '/quiet', 'attributes' => ['a', 'b', 'c', 'd']],
            ['exception' => ClientException::class, 'endpoint' => '/quiet', 'attributes' => [['a', 'b', 'c', 'd']]],
            ['exception' => ClientException::class, 'endpoint' => '/quiet', 'attributes' => [['a', 'b', 'c', 'd'], ['a', 'b', 'c']]],
            // Wrong type of endpoint
            ['exception' => QueryException::class, 'endpoint' => 1, 'attributes' => null],
        ];
    }

    /**
     * @dataProvider queryExceptionDataProvider
     *
     * @param string $exception
     * @param mixed  $endpoint
     * @param mixed  $attributes
     *
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function testQueryException(string $exception, $endpoint, $attributes): void
    {
        $this->expectException($exception);
        $this->client->query($endpoint, $attributes);
    }

    public function testExportMethod(): void
    {
        $result = $this->client->export();
        $this->assertNotEmpty($result);
    }

    public function testExportQuery(): void
    {
        $result = $this->client->query('/export');
        $this->assertNotEmpty($result);
    }
}
