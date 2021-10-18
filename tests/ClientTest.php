<?php

namespace RouterOS\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use RouterOS\Client;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Config;
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
     * @var \RouterOS\Client
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
            'ssh_port' => (int) getenv('ROS_SSH_PORT'),
        ];

        $this->client = new class($this->config) extends Client {
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
    public function test_construct_configObjectSetters(): void
    {
        try {
            $config = new Config();
            $config
                ->set('user', $this->config['user'])
                ->set('pass', $this->config['pass'])
                ->set('host', $this->config['host']);

            $obj = new Client($config);
            self::assertIsObject($obj);
            $socket = $obj->getSocket();
            self::assertIsResource($socket);
        } catch (Exception $e) {
            self::assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    /**
     * Configuration array passed to Config object, then Config object passed to Client object
     */
    public function test_construct_configObjectArray(): void
    {
        try {
            $config = new Config($this->config);
            $obj    = new Client($config);
            self::assertIsObject($obj);
            $socket = $obj->getSocket();
            self::assertIsResource($socket);
        } catch (Exception $e) {
            self::assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    /**
     * Configuration array passed directly to client object
     */
    public function test_construct_configClient(): void
    {
        try {
            $obj = new Client($this->config);
            self::assertIsObject($obj);
            $socket = $obj->getSocket();
            self::assertIsResource($socket);
        } catch (Exception $e) {
            self::assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    /**
     * If "host" option is not found in configuration
     */
    public function test_construct_exceptionNoHost(): void
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
    public function test_construct_exceptionBadPort(): void
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
    public function test_construct_exceptionUnableToConnect(): void
    {
        $this->expectException(ConnectException::class);
        $this->expectExceptionCode(111);

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
    public function test_construct_exceptionBadCredentials(): void
    {
        $this->expectException(BadCredentialsException::class);

        new Client([
            'user'     => $this->config['user'],
            'pass'     => 'admin2',
            'host'     => $this->config['host'],
            'attempts' => 2,
        ]);
    }

    public function test_construct_legacyDefault(): void
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
        } catch (Exception $e) {
            self::assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    /**
     * Test non legacy connection on legacy router (pre 6.43)
     *
     * login() method recognise legacy router response and swap to legacy mode
     */
    public function test_construct_legacyAutodetect(): void
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
        } catch (Exception $e) {
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
    public function test_pregResponse(string $line, array $result): void
    {
        $matches = [];
        $this->client->pregResponse($line, $matches);
        self::assertEquals($matches, $result);
    }

    public function test_query_read(): void
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
        self::assertEquals('*1', $read[0]['.id']);

        /*
         * Build query with tag
         */

        $read = $this->client->query('/system/package/print', null, null, 'zzzz')->read();

        // self::assertCount(13, $read);
        self::assertEquals('zzzz', $read[0]['tag']);

        /*
         * Build query with option count
         */

        $read = $this->client->query('/interface/monitor-traffic')->read(true, ['count' => 3]);
        self::assertCount(3, $read);
    }

    public function test_readAsIterator(): void
    {
        $result = $this->client->query('/system/package/print')->readAsIterator();
        self::assertIsObject($result);
    }

    public function test_query_readWithoutParsing(): void
    {
        $readTrap = $this->client->query('/interface')->read(false);
        self::assertCount(3, $readTrap);
        self::assertEquals('!trap', $readTrap[0]);
    }

    public function test_query_fatalError(): void
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
    public function test_query_exception(string $exception, int $code, $endpoint, $attributes): void
    {
        $this->expectException($exception);
        $this->expectExceptionCode($code);
        $this->client->query($endpoint, $attributes);
    }

    public function test_export_asMethod(): void
    {
        if (!in_array(gethostname(), ['pasha-lt', 'pasha-pc'])) {
            self::markTestSkipped('Travis does not allow to use SSH protocol on testing stage.');
        }

        $result = $this->client->export();
        self::assertNotEmpty($result);
    }

    public function test_export_asQuery(): void
    {
        if (!in_array(gethostname(), ['pasha-lt', 'pasha-pc'])) {
            self::markTestSkipped('Travis does not allow to use SSH protocol on testing stage.');
        }

        $result = $this->client->query('/export');
        self::assertNotEmpty($result);
    }
}
