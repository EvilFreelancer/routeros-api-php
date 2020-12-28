<?php

namespace RouterOS;

use DivineOmega\SSHConnection\SSHConnection;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Interfaces\ClientInterface;
use RouterOS\Interfaces\QueryInterface;
use RouterOS\Helpers\ArrayHelper;
use function array_keys;
use function array_shift;
use function chr;
use function count;
use function is_array;
use function md5;
use function pack;
use function preg_match_all;
use function sleep;
use function trim;

/**
 * Class Client for RouterOS management
 *
 * @package RouterOS
 * @since   0.1
 */
class Client implements Interfaces\ClientInterface
{
    use SocketTrait, ShortsTrait;

    /**
     * Configuration of connection
     *
     * @var \RouterOS\Config
     */
    private $config;

    /**
     * API communication object
     *
     * @var \RouterOS\APIConnector
     */
    private $connector;

    /**
     * Some strings with custom output
     *
     * @var string
     */
    private $customOutput;

    /**
     * Client constructor.
     *
     * @param array|\RouterOS\Interfaces\ConfigInterface $config      Array with configuration or Config object
     * @param bool                                       $autoConnect If false it will skip auto-connect stage if not need to instantiate connection
     *
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConnectException
     * @throws \RouterOS\Exceptions\BadCredentialsException
     * @throws \RouterOS\Exceptions\ConfigException
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function __construct($config, bool $autoConnect = true)
    {
        // If array then need create object
        if (is_array($config)) {
            $config = new Config($config);
        }

        // Check for important keys
        if (true !== $key = ArrayHelper::checkIfKeysNotExist(['host', 'user', 'pass'], $config->getParameters())) {
            throw new ConfigException("One or few parameters '$key' of Config is not set or empty");
        }

        // Save config if everything is okay
        $this->config = $config;

        // Skip next step if not need to instantiate connection
        if (false === $autoConnect) {
            return;
        }

        // Throw error if cannot to connect
        if (false === $this->connect()) {
            throw new ConnectException('Unable to connect to ' . $config->get('host') . ':' . $config->get('port'));
        }
    }

    /**
     * Get some parameter from config
     *
     * @param string $parameter Name of required parameter
     *
     * @return mixed
     * @throws \RouterOS\Exceptions\ConfigException
     */
    private function config(string $parameter)
    {
        return $this->config->get($parameter);
    }

    /**
     * Send write query to RouterOS (modern version of write)
     *
     * @param array|string|\RouterOS\Interfaces\QueryInterface $endpoint   Path of API query or Query object
     * @param array|null                                       $where      List of where filters
     * @param string|null                                      $operations Some operations which need make on response
     * @param string|null                                      $tag        Mark query with tag
     *
     * @return \RouterOS\Interfaces\ClientInterface
     * @throws \RouterOS\Exceptions\QueryException
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     * @since 1.0.0
     */
    public function query($endpoint, array $where = null, string $operations = null, string $tag = null): ClientInterface
    {
        // If endpoint is string then build Query object
        $query = ($endpoint instanceof Query)
            ? $endpoint
            : new Query($endpoint);

        // Parse where array
        if (!empty($where)) {

            // If array is multidimensional, then parse each line
            if (is_array($where[0])) {
                foreach ($where as $item) {
                    $query = $this->preQuery($item, $query);
                }
            } else {
                $query = $this->preQuery($where, $query);
            }

        }

        // Append operations if set
        if (!empty($operations)) {
            $query->operations($operations);
        }

        // Append tag if set
        if (!empty($tag)) {
            $query->tag($tag);
        }

        // Submit query to RouterOS
        return $this->writeRAW($query);
    }

    /**
     * Query helper
     *
     * @param array                               $item
     * @param \RouterOS\Interfaces\QueryInterface $query
     *
     * @return \RouterOS\Query
     * @throws \RouterOS\Exceptions\QueryException
     * @throws \RouterOS\Exceptions\ClientException
     */
    private function preQuery(array $item, QueryInterface $query): QueryInterface
    {
        // Null by default
        $key      = null;
        $operator = null;
        $value    = null;

        switch (count($item)) {
            case 1:
                [$key] = $item;
                break;
            case 2:
                [$key, $operator] = $item;
                break;
            case 3:
                [$key, $operator, $value] = $item;
                break;
            default:
                throw new ClientException('From 1 to 3 parameters of "where" condition is allowed');
        }

        return $query->where($key, $operator, $value);
    }

    /**
     * Send write query object to RouterOS
     *
     * @param \RouterOS\Interfaces\QueryInterface $query
     *
     * @return \RouterOS\Interfaces\ClientInterface
     * @throws \RouterOS\Exceptions\QueryException
     * @throws \RouterOS\Exceptions\ConfigException
     * @since 1.0.0
     */
    private function writeRAW(QueryInterface $query): ClientInterface
    {
        $commands = $query->getQuery();

        // Check if first command is export
        if (0 === strpos($commands[0], '/export')) {

            // Convert export command with all arguments to valid SSH command
            $arguments = explode('/', $commands[0]);
            unset($arguments[1]);
            $arguments = implode(' ', $arguments);

            // Call the router via ssh and store output of export
            $this->customOutput = $this->export($arguments);

            // Return current object
            return $this;
        }

        // Send commands via loop to router
        foreach ($commands as $command) {
            $this->connector->writeWord(trim($command));
        }

        // Write zero-terminator (empty string)
        $this->connector->writeWord('');

        // Return current object
        return $this;
    }

    /**
     * Read RAW response from RouterOS, it can be /export command results also, not only array from API
     *
     * @param array $options Additional options
     *
     * @return array|string
     * @since 1.0.0
     */
    public function readRAW(array $options = [])
    {
        // By default response is empty
        $response = [];
        // We have to wait a !done or !fatal
        $lastReply = false;
        // Count !re in response
        $countResponse = 0;

        // Convert strings to array and return results
        if ($this->isCustomOutput()) {
            // Return RAW configuration
            return $this->customOutput;
        }

        // Read answer from socket in loop
        while (true) {
            $word = $this->connector->readWord();

            //Limit response number to finish the read
            if (isset($options['count']) && $countResponse >= (int) $options['count']) {
                $lastReply = true;
            }

            if ('' === $word) {
                if ($lastReply) {
                    // We received a !done or !fatal message in a precedent loop
                    // response is complete
                    break;
                }
                // We did not receive the !done or !fatal message
                // This 0 length message is the end of a reply !re or !trap
                // We have to wait the router to send a !done or !fatal reply followed by optionals values and a 0 length message
                continue;
            }

            // Save output line to response array
            $response[] = $word;

            // If we get a !done or !fatal line in response, we are now ready to finish the read
            // but we need to wait a 0 length message, switch the flag
            if ('!done' === $word || '!fatal' === $word) {
                $lastReply = true;
            }

            // If we get a !re line in response, we increment the variable
            if ('!re' === $word) {
                $countResponse++;
            }
        }

        // Parse results and return
        return $response;
    }

    /**
     * Read answer from server after query was executed
     *
     * A Mikrotik reply is formed of blocks
     * Each block starts with a word, one of ('!re', '!trap', '!done', '!fatal')
     * Each block end with an zero byte (empty line)
     * Reply ends with a complete !done or !fatal block (ended with 'empty line')
     * A !fatal block precedes TCP connexion close
     *
     * @param bool  $parse   If need parse output to array
     * @param array $options Additional options
     *
     * @return mixed
     */
    public function read(bool $parse = true, array $options = [])
    {
        // Read RAW response
        $response = $this->readRAW($options);

        // Return RAW configuration if custom output is set
        if ($this->isCustomOutput()) {
            $this->customOutput = null;
            return $response;
        }

        // Parse results and return
        return $parse ? $this->rosario($response) : $response;
    }

    /**
     * Read using Iterators to improve performance on large dataset
     *
     * @param array $options Additional options
     *
     * @return \RouterOS\ResponseIterator
     * @since 1.0.0
     */
    public function readAsIterator(array $options = []): ResponseIterator
    {
        return new ResponseIterator($this, $options);
    }

    /**
     * This method was created by memory save reasons, it convert response
     * from RouterOS to readable array in safe way.
     *
     * @param array $raw Array RAW response from server
     *
     * @return mixed
     *
     * Based on RouterOSResponseArray solution by @arily
     *
     * @see   https://github.com/arily/RouterOSResponseArray
     * @since 1.0.0
     */
    private function rosario(array $raw): array
    {
        // This RAW should't be an error
        $positions = array_keys($raw, '!re');
        $count     = count($raw);
        $result    = [];

        if (isset($positions[1])) {

            foreach ($positions as $key => $position) {
                // Get length of future block
                $length = isset($positions[$key + 1])
                    ? $positions[$key + 1] - $position + 1
                    : $count - $position;

                // Convert array to simple items
                $item = [];
                for ($i = 1; $i < $length; $i++) {
                    $item[] = array_shift($raw);
                }

                // Save as result
                $result[] = $this->parseResponse($item)[0];
            }

        } else {
            $result = $this->parseResponse($raw);
        }

        return $result;
    }

    /**
     * Parse response from Router OS
     *
     * @param array $response Response data
     *
     * @return array Array with parsed data
     */
    public function parseResponse(array $response): array
    {
        $result = [];
        $i      = -1;
        $lines  = count($response);
        foreach ($response as $key => $value) {
            switch ($value) {
                case '!re':
                    $i++;
                    break;
                case '!fatal':
                    $result = $response;
                    break 2;
                case '!trap':
                case '!done':
                    // Check for =ret=, .tag and any other following messages
                    for ($j = $key + 1; $j <= $lines; $j++) {
                        // If we have lines after current one
                        if (isset($response[$j])) {
                            $this->preParseResponse($response[$j], $result, $matches);
                        }
                    }
                    break 2;
                default:
                    $this->preParseResponse($value, $result, $matches, $i);
                    break;
            }
        }
        return $result;
    }

    /**
     * Response helper
     *
     * @param string     $value    Value which should be parsed
     * @param array      $result   Array with parsed response
     * @param array|null $matches  Matched words
     * @param string|int $iterator Type of iterations or number of item
     */
    private function preParseResponse(string $value, array &$result, ?array &$matches, $iterator = 'after'): void
    {
        $this->pregResponse($value, $matches);
        if (isset($matches[1][0], $matches[2][0])) {
            $result[$iterator][$matches[1][0]] = $matches[2][0];
        }
    }

    /**
     * Parse result from RouterOS by regular expression
     *
     * @param string     $value
     * @param array|null $matches
     */
    protected function pregResponse(string $value, ?array &$matches): void
    {
        preg_match_all('/^[=|.]([.\w-]+)=(.*)/', $value, $matches);
    }

    /**
     * Authorization logic
     *
     * @param bool $legacyRetry Retry login if we detect legacy version of RouterOS
     *
     * @return bool
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\BadCredentialsException
     * @throws \RouterOS\Exceptions\ConfigException
     * @throws \RouterOS\Exceptions\QueryException
     */
    private function login(bool $legacyRetry = false): bool
    {
        // If legacy login scheme is enabled
        if ($this->config('legacy')) {
            // For the first we need get hash with salt
            $response = $this->query('/login')->read();

            // Now need use this hash for authorization
            $query = new Query('/login', [
                '=name=' . $this->config('user'),
                '=response=00' . md5(chr(0) . $this->config('pass') . pack('H*', $response['after']['ret'])),
            ]);
        } else {
            // Just login with our credentials
            $query = new Query('/login', [
                '=name=' . $this->config('user'),
                '=password=' . $this->config('pass'),
            ]);

            // If we set modern auth scheme but router with legacy firmware then need to retry query,
            // but need to prevent endless loop
            $legacyRetry = true;
        }

        // Execute query and get response
        $response = $this->query($query)->read(false);

        // if:
        //  - we have more than one response
        //  - response is '!done'
        // => problem with legacy version, swap it and retry
        // Only tested with ROS pre 6.43, will test with post 6.43 => this could make legacy parameter obsolete?
        if ($legacyRetry && $this->isLegacy($response)) {
            $this->config->set('legacy', true);
            return $this->login();
        }

        // If RouterOS answered with invalid credentials then throw error
        if (!empty($response[0]) && '!trap' === $response[0]) {
            throw new BadCredentialsException('Invalid user name or password');
        }

        // Return true if we have only one line from server and this line is !done
        return (1 === count($response)) && isset($response[0]) && ('!done' === $response[0]);
    }

    /**
     * Detect by login request if firmware is legacy
     *
     * @param array $response
     *
     * @return bool
     * @throws \RouterOS\Exceptions\ConfigException
     */
    private function isLegacy(array $response): bool
    {
        return count($response) > 1 && '!done' === $response[0] && !$this->config('legacy');
    }

    /**
     * Connect to socket server
     *
     * @return bool
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function connect(): bool
    {
        // By default we not connected
        $connected = false;

        // Few attempts in loop
        for ($attempt = 1; $attempt <= $this->config('attempts'); $attempt++) {

            // Initiate socket session
            $this->openSocket();

            // If socket is active
            if (null !== $this->getSocket()) {
                $this->connector = new APIConnector(new Streams\ResourceStream($this->getSocket()));
                // If we logged in then exit from loop
                if (true === $this->login()) {
                    $connected = true;
                    break;
                }

                // Else close socket and start from begin
                $this->closeSocket();
            }

            // Sleep some time between tries
            sleep($this->config('delay'));
        }

        // Return status of connection
        return $connected;
    }

    /**
     * Check if custom output is not empty
     *
     * @return bool
     */
    private function isCustomOutput(): bool
    {
        return null !== $this->customOutput;
    }

    /**
     * Execute export command on remote host, it also will be used
     * if "/export" command passed to query.
     *
     * @param string|null $arguments String with arguments which should be passed to export command
     *
     * @return string
     * @throws \RouterOS\Exceptions\ConfigException
     * @since 1.3.0
     */
    public function export(string $arguments = null): string
    {
        // Connect to remote host
        $connection =
            (new SSHConnection())
                ->timeout($this->config('timeout'))
                ->to($this->config('host'))
                ->onPort($this->config('ssh_port'))
                ->as($this->config('user') . '+etc')
                ->withPassword($this->config('pass'))
                ->connect();

        // Run export command
        $command = $connection->run('/export' . ' ' . $arguments);

        // Return the output
        return $command->getOutput();
    }
}
