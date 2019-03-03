<?php

namespace RouterOS;

use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Helpers\ArrayHelper;

/**
 * Class Client for RouterOS management
 *
 * @package RouterOS
 * @since   0.1
 */
class Client implements Interfaces\ClientInterface
{
    use SocketTrait;

    /**
     * Socket resource
     *
     * @var resource|null
     */
    private $_socket;

    /**
     * Code of error
     *
     * @var int
     */
    private $_socket_err_num;

    /**
     * Description of socket error
     *
     * @var string
     */
    private $_socket_err_str;

    /**
     * Configuration of connection
     *
     * @var \RouterOS\Config
     */
    private $_config;

    /**
     * Client constructor.
     *
     * @param   array|\RouterOS\Config $config
     * @throws  \RouterOS\Exceptions\ClientException
     * @throws  \RouterOS\Exceptions\ConfigException
     * @throws  \RouterOS\Exceptions\QueryException
     */
    public function __construct($config)
    {
        // If array then need create object
        if (\is_array($config)) {
            $config = new Config($config);
        }

        // Check for important keys
        if (true !== $key = ArrayHelper::checkIfKeysNotExist(['host', 'user', 'pass'], $config->getParameters())) {
            throw new ConfigException("One or few parameters '$key' of Config is not set or empty");
        }

        // Save config if everything is okay
        $this->setConfig($config);

        // Throw error if cannot to connect
        if (false === $this->connect()) {
            throw new ClientException('Unable to connect to ' . $config->get('host') . ':' . $config->get('port'));
        }
    }

    /**
     * Get some parameter from config
     *
     * @param   string $parameter Name of required parameter
     * @return  mixed
     * @throws  \RouterOS\Exceptions\ConfigException
     */
    private function config(string $parameter)
    {
        return $this->_config->get($parameter);
    }

    /**
     * Return socket resource if is exist
     *
     * @return  \RouterOS\Config
     * @since   0.6
     */
    public function getConfig(): Config
    {
        return $this->_config;
    }

    /**
     * Set configuration of client
     *
     * @param   \RouterOS\Config $config
     * @since   0.7
     */
    public function setConfig(Config $config)
    {
        $this->_config = $config;
    }

    /**
     * Encode given length in RouterOS format
     *
     * @param   string $string
     * @return  string Encoded length
     * @throws  \RouterOS\Exceptions\ClientException
     */
    private function encodeLength(string $string): string
    {
        $length = \strlen($string);

        if ($length < 128) {
            $orig_length = $length;
            $offset      = -1;
        } elseif ($length < 16384) {
            $orig_length = $length | 0x8000;
            $offset      = -2;
        } elseif ($length < 2097152) {
            $orig_length = $length | 0xC00000;
            $offset      = -3;
        } elseif ($length < 268435456) {
            $orig_length = $length | 0xE0000000;
            $offset      = -4;
        } else {
            throw new ClientException("Unable to encode length of '$string'");
        }

        // Pack string to binary format
        $result = pack('I*', $orig_length);
        // Parse binary string to array
        $result = str_split($result);
        // Reverse array
        $result = array_reverse($result);
        // Extract values from offset to end of array
        $result = \array_slice($result, $offset);

        // Sew items into one line
        $output = null;
        foreach ($result as $item) {
            $output .= $item;
        }

        return $output;
    }

    /**
     * Read length of line
     *
     * @param   int $byte
     * @return  int
     */
    private function getLength(int $byte): int
    {
        // If the first bit is set then we need to remove the first four bits, shift left 8
        // and then read another byte in.
        // We repeat this for the second and third bits.
        // If the fourth bit is set, we need to remove anything left in the first byte
        // and then read in yet another byte.
        if ($byte & 128) {
            if (($byte & 192) === 128) {
                $length = (($byte & 63) << 8) + \ord(fread($this->_socket, 1));
            } elseif (($byte & 224) === 192) {
                $length = (($byte & 31) << 8) + \ord(fread($this->_socket, 1));
                $length = ($length << 8) + \ord(fread($this->_socket, 1));
            } elseif (($byte & 240) === 224) {
                $length = (($byte & 15) << 8) + \ord(fread($this->_socket, 1));
                $length = ($length << 8) + \ord(fread($this->_socket, 1));
                $length = ($length << 8) + \ord(fread($this->_socket, 1));
            } else {
                $length = \ord(fread($this->_socket, 1));
                $length = ($length << 8) + \ord(fread($this->_socket, 1)) * 3;
                $length = ($length << 8) + \ord(fread($this->_socket, 1));
                $length = ($length << 8) + \ord(fread($this->_socket, 1));
            }
        } else {
            $length = $byte;
        }
        return $length;
    }

    /**
     * Send write query to RouterOS (with or without tag)
     *
     * @param   string|array|\RouterOS\Query $query
     * @return  \RouterOS\Client
     * @throws  \RouterOS\Exceptions\ClientException
     * @throws  \RouterOS\Exceptions\QueryException
     */
    public function write($query): Client
    {
        if (\is_string($query)) {
            $query = new Query($query);
        } elseif (\is_array($query)) {
            $endpoint = array_shift($query);
            $query    = new Query($endpoint, $query);
        }

        if (!$query instanceof Query) {
            throw new QueryException('Parameters cannot be processed');
        }

        // Send commands via loop to router
        foreach ($query->getQuery() as $command) {
            $command = trim($command);
            fwrite($this->_socket, $this->encodeLength($command) . $command);
        }

        // Write zero-terminator
        fwrite($this->_socket, \chr(0));

        return $this;
    }

    /**
     * Read answer from server after query was executed
     *
     * @param   bool $parse
     * @return  array
     */
    public function read(bool $parse = true): array
    {
        // By default response is empty
        $response = [];

        // Read answer from socket in loop
        while (true) {
            // Read the first byte of input which gives us some or all of the length
            // of the remaining reply.
            $byte   = fread($this->_socket, 1);
            $length = $this->getLength(\ord($byte));

            // Save only non empty strings
            if ($length > 0) {
                // Save output line to response array
                $response[] = stream_get_contents($this->_socket, $length);
            }

            // If we get a !done line in response, change state of $isDone variable
            $isDone = ('!done' === end($response));

            // Get status about latest operation
            $status = stream_get_meta_data($this->_socket);

            // If we do not have unread bytes from socket or <-same and if done, then exit from loop
            if ((!$status['unread_bytes']) || (!$status['unread_bytes'] && $isDone)) {
                break;
            }
        }

        // Parse results and return
        return $parse ? $this->parseResponse($response) : $response;
    }

    /**
     * Alias for ->write() method
     *
     * @param   string|array|\RouterOS\Query $query
     * @return  \RouterOS\Client
     * @throws  \RouterOS\Exceptions\ClientException
     * @throws  \RouterOS\Exceptions\QueryException
     */
    public function w($query): Client
    {
        return $this->write($query);
    }

    /**
     * Alias for ->read() method
     *
     * @param   bool $parse
     * @return  array
     * @since   0.7
     */
    public function r(bool $parse = true): array
    {
        return $this->read($parse);
    }

    /**
     * Alias for ->write()->read() combination of methods
     *
     * @param   string|array|\RouterOS\Query $query
     * @param   bool                         $parse
     * @return  array
     * @throws  \RouterOS\Exceptions\ClientException
     * @throws  \RouterOS\Exceptions\QueryException
     * @since   0.6
     */
    public function wr($query, bool $parse = true): array
    {
        return $this->write($query)->read($parse);
    }

    /**
     * Parse response from Router OS
     *
     * @param   array $response Response data
     * @return  array Array with parsed data
     */
    private function parseResponse(array $response): array
    {
        $result = [];
        $i      = -1;
        $lines  = \count($response);
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
                            $this->pregResponse($response[$j], $matches);
                            if (!empty($matches)) {
                                $result['after'][$matches[1][0]] = $matches[2][0];
                            }
                        }
                    }
                    break 2;
                default:
                    $this->pregResponse($value, $matches);
                    if (!empty($matches)) {
                        $result[$i][$matches[1][0]] = $matches[2][0];
                    }
                    break;
            }
        }
        return $result;
    }

    /**
     * Parse result from RouterOS by regular expression
     *
     * @param   string $value
     * @param   array  $matches
     */
    private function pregResponse(string $value, &$matches)
    {
        preg_match_all('/^[=|\.](.*)=(.*)/', $value, $matches);
    }

    /**
     * Authorization logic
     *
     * @return  bool
     * @throws  \RouterOS\Exceptions\ClientException
     * @throws  \RouterOS\Exceptions\ConfigException
     * @throws  \RouterOS\Exceptions\QueryException
     */
    private function login(): bool
    {
        // If legacy login scheme is enabled
        if ($this->config('legacy')) {
            // For the first we need get hash with salt
            $query    = new Query('/login');
            $response = $this->write($query)->read();

            // Now need use this hash for authorization
            $query = (new Query('/login'))
                ->add('=name=' . $this->config('user'))
                ->add('=response=00' . md5(\chr(0) . $this->config('pass') . pack('H*', $response['after']['ret'])));
        } else {
            // Just login with our credentials
            $query = (new Query('/login'))
                ->add('=name=' . $this->config('user'))
                ->add('=password=' . $this->config('pass'));
        }

        // Execute query and get response
        $response = $this->write($query)->read(false);

        // Return true if we have only one line from server and this line is !done
        return isset($response[0]) && $response[0] === '!done';
    }

    /**
     * Connect to socket server
     *
     * @return  bool
     * @throws  \RouterOS\Exceptions\ClientException
     * @throws  \RouterOS\Exceptions\ConfigException
     * @throws  \RouterOS\Exceptions\QueryException
     */
    private function connect(): bool
    {
        // By default we not connected
        $connected = false;

        // Few attempts in loop
        for ($attempt = 1; $attempt <= $this->config('attempts'); $attempt++) {

            // Initiate socket session
            $this->openSocket();

            // If socket is active
            if (null !== $this->getSocket()) {

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

}
