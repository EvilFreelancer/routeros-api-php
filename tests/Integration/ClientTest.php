<?php

namespace RouterOS\Tests\Integration;

use PHPUnit\Framework\TestCase;
use RouterOS\Client;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Config;
use RouterOS\Query;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\BadCredentialsException;

class ClientTest extends TestCase
{
    /**
     * @var array
     */
    public $config;

    /**
     * @var Client
     */
    public $client;

    /**
     * @var int
     */
    public $portModern;

    /**
     * @var int
     */
    public $portLegacy;

    public function setUp(): void
    {
        $this->config = [
            'user'     => getenv('ROS_USER'),
            'pass'     => getenv('ROS_PASS'),
            'host'     => getenv('ROS_HOST_MODERN'),
            'port'     => (int) getenv('ROS_PORT_MODERN'),
            'ssh_port' => (int) getenv('ROS_SSH_PORT'),
        ];

        $this->client =

            new class($this->config) extends Client {
                // Convert protected method to public
                public function pregResponse(string $value, ?array &$matches): void
                {
                    parent::pregResponse($value, $matches);
                }
            };

        $this->portModern = (int) getenv('ROS_PORT_MODERN');
        $this->portLegacy = (int) getenv('ROS_PORT_LEGACY');
    }

    /**
     * Config object changed via setters then passed to Client object
     */
    public function testConstructConfigObjectSetters(): void
    {
        try {
            $config = new Config();
            $config
                ->set('user', $this->config['user'])
                ->set('pass', $this->config['pass'])
                ->set('host', $this->config['host'])
                ->set('port', $this->config['port']);

            $obj = new Client($config);
            self::assertIsObject($obj);
            $socket = $obj->getSocket();
            self::assertIsResource($socket);
        } catch (\Exception $e) {
            self::assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    /**
     * Configuration array passed to Config object, then Config object passed to Client object
     */
    public function testConstructConfigObjectArray(): void
    {
        try {
            $config = new Config($this->config);
            $obj    = new Client($config);
            self::assertIsObject($obj);
            $socket = $obj->getSocket();
            self::assertIsResource($socket);
        } catch (\Exception $e) {
            self::assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    /**
     * Configuration array passed directly to client object
     */
    public function testConstructConfigClient(): void
    {
        try {
            $obj = new Client($this->config);
            self::assertIsObject($obj);
            $socket = $obj->getSocket();
            self::assertIsResource($socket);
        } catch (\Exception $e) {
            self::assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    /**
     * If "host" option is not found in configuration
     */
    public function testConstructExceptionNoHost(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("One or few parameters 'host' of Config is not set or empty");

        new Client([
            'user' => $this->config['user'],
            'pass' => $this->config['pass'],
        ]);
    }

    /**
     * If we try to use invalid port number
     */
    public function testConstructExceptionBadPort(): void
    {
        $this->expectException(ConnectException::class);

        new Client([
            'host'     => '127.0.0.1',
            'port'     => 123456,
            'attempts' => 0,
            'user'     => $this->config['user'],
            'pass'     => $this->config['pass'],
        ]);
    }

    /**
     * If we can't connect to router, for example bacause wrong port provided
     */
    public function testConstructExceptionUnableToConnect(): void
    {
        $this->expectException(ConnectException::class);
        $this->expectExceptionCode(PHP_OS_FAMILY === 'Windows' ? 10061 : 111);

        new Client([
            'user'     => $this->config['user'],
            'pass'     => $this->config['pass'],
            'host'     => $this->config['host'],
            'port'     => 11111,
            'attempts' => 2,
        ]);
    }

    /**
     * If we can connecto router, but wrong password or login used
     */
    public function testConstructExceptionBadCredentials(): void
    {
        $this->expectException(BadCredentialsException::class);

        new Client([
            'user'     => $this->config['user'],
            'pass'     => 'admin2',
            'host'     => $this->config['host'],
            'port'     => $this->config['port'],
            'attempts' => 2,
        ]);
    }

    public function testConstructLegacyDefault(): void
    {
        $this->markTestSkipped('There is no reason to test legacy anymore, test will be removed in future.');

        try {
            $obj = new Client([
                'user'   => $this->config['user'],
                'pass'   => $this->config['pass'],
                'host'   => getenv('ROS_HOST_LEGACY'),
                'port'   => $this->portLegacy,
                'legacy' => true,
            ]);
            self::assertIsObject($obj);
        } catch (\Exception $e) {
            self::assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    /**
     * Test non legacy connection on legacy router (pre 6.43)
     *
     * login() method recognise legacy router response and swap to legacy mode
     */
    public function testConstructLegacyAutodetect(): void
    {
        $this->markTestSkipped('There is no reason to test legacy anymore, test will be removed in future.');

        try {
            $obj = new Client([
                'user'   => $this->config['user'],
                'pass'   => $this->config['pass'],
                'host'   => $this->config['host'],
                'port'   => $this->portLegacy,
                'legacy' => false,
            ]);
            self::assertIsObject($obj);
        } catch (\Exception $e) {
            self::assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    public function pregResponseDataProvider(): array
    {
        return [
            ['line' => '=.id=1', 'result' => [['=.id=1'], ['.id'], [1]]],
            ['line' => '=name=kjhasdrlkh=5468=3456kh3l45', 'result' => [['=name=kjhasdrlkh=5468=3456kh3l45'], ['name'], ['kjhasdrlkh=5468=3456kh3l45']]],
            ['line' => '=name==d===efault=a===123sadf=3=3===', 'result' => [['=name==d===efault=a===123sadf=3=3==='], ['name'], ['=d===efault=a===123sadf=3=3===']]],
            ['line' => '=name============', 'result' => [['=name============'], ['name'], ['===========']]],
            ['line' => '=on-login={:liahdf =aasdf(zz)a;ldfj}', 'result' => [['=on-login={:liahdf =aasdf(zz)a;ldfj}'], ['on-login'], ['{:liahdf =aasdf(zz)a;ldfj}']]],
        ];
    }

    /**
     * @dataProvider pregResponseDataProvider
     *
     * @param string $line
     * @param array  $result
     */
    public function testPregResponse(string $line, array $result): void
    {
        $matches = [];
        $this->client->pregResponse($line, $matches);
        self::assertEquals($matches, $result);
    }

    public function testQueryRead(): void
    {
        /*
         * Build query with where
         */

        $read = $this->client->query('/system/package/print', ['name'])->read();
        self::assertNotEmpty($read);

        $read = $this->client->query('/system/package/print', ['.id', '*1'])->read();
        self::assertCount(1, $read);

        $read = $this->client->query('/system/package/print', ['.id', '=', '*1'])->read();
        self::assertCount(1, $read);

        $read = $this->client->query('/system/package/print', [['name']])->read();
        self::assertNotEmpty($read);

        $read = $this->client->query('/system/package/print', [['.id', '*1']])->read();
        self::assertCount(1, $read);

        $read = $this->client->query('/system/package/print', [['.id', '=', '*1']])->read();
        self::assertCount(1, $read);

        /*
         * Build query with operations
         */

        $read = $this->client->query('/interface/print', [
            ['type', 'ether'],
            ['type', 'vlan'],
        ], '|')->read();
        self::assertCount(1, $read);
        self::assertArrayHasKey('.id', $read[0]);
        self::assertSame('ether', $read[0]['type']);

        /*
         * Build query with tag
         */

        $read = $this->client->query('/system/package/print', null, null, 'zzzz')->read();

        // self::assertCount(13, $read);
        self::assertEquals('zzzz', $read[0]['tag']);

        /*
         * Build query with option count
         */

        $read = $this->client->query(new Query('/interface/monitor-traffic', [
            '=interface=ether1',
            '=once=',
        ]))->read();
        self::assertCount(1, $read);
        self::assertArrayHasKey('rx-bits-per-second', $read[0]);
    }

    public function testReadAsIterator(): void
    {
        $result = $this->client->query('/system/package/print')->readAsIterator();
        self::assertIsObject($result);
    }

    public function testQueryReadWithoutParsing(): void
    {
        $readTrap = $this->client->query('/interface')->read(false);
        self::assertCount(3, $readTrap);
        self::assertEquals('!trap', $readTrap[0]);
    }

    public function testQueryFatalError(): void
    {
        $readTrap = $this->client->query('/quit')->read();
        self::assertCount(2, $readTrap);
        self::assertEquals('!fatal', $readTrap[0]);
    }

    public function queryExceptionDataProvider(): array
    {
        return [
            // Wrong amount of parameters
            ['exception' => ClientException::class, 'code' => 0, 'endpoint' => '/quiet', 'attributes' => [[]]],
            ['exception' => ClientException::class, 'code' => 0, 'endpoint' => '/quiet', 'attributes' => [[], ['a', 'b', 'c']]],
            ['exception' => ClientException::class, 'code' => 0, 'endpoint' => '/quiet', 'attributes' => ['a', 'b', 'c', 'd']],
            ['exception' => ClientException::class, 'code' => 0, 'endpoint' => '/quiet', 'attributes' => [['a', 'b', 'c', 'd']]],
            ['exception' => ClientException::class, 'code' => 0, 'endpoint' => '/quiet', 'attributes' => [['a', 'b', 'c', 'd'], ['a', 'b', 'c']]],
            // Wrong type of endpoint
            ['exception' => QueryException::class, 'code' => 0, 'endpoint' => 1, 'attributes' => null],
        ];
    }

    /**
     * @dataProvider queryExceptionDataProvider
     *
     * @param string $exception
     * @param mixed  $endpoint
     * @param mixed  $attributes
     */
    public function testQueryException(string $exception, int $code, $endpoint, $attributes): void
    {
        $this->expectException($exception);
        $this->expectExceptionCode($code);
        $this->client->query($endpoint, $attributes);
    }

    public function testExportAsMethod(): void
    {
        if (!in_array(gethostname(), ['pasha-lt', 'pasha-pc'])) {
            self::markTestSkipped('Travis does not allow to use SSH protocol on testing stage.');
        }

        $result = $this->client->export();
        self::assertNotEmpty($result);
    }

    public function testExportAsQuery(): void
    {
        if (!in_array(gethostname(), ['pasha-lt', 'pasha-pc'])) {
            self::markTestSkipped('Travis does not allow to use SSH protocol on testing stage.');
        }

        $result = $this->client->query('/export');
        self::assertNotEmpty($result);
    }

    public function testParseResponseEmptyArrayHandling(): void
    {
        // Simulate responses that could come from nested script execution
        $emptyResponse = [];
        $result        = $this->client->parseResponse($emptyResponse);
        self::assertIsArray($result);
        self::assertEmpty($result);

        // Response with only control words (no data)
        $doneOnlyResponse = ['!done'];
        $result           = $this->client->parseResponse($doneOnlyResponse);
        self::assertIsArray($result);

        // Response with trap (error case)
        $trapResponse = ['!trap', '=message=no such command'];
        $result       = $this->client->parseResponse($trapResponse);
        self::assertIsArray($result);
    }
}
