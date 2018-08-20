<?php

namespace RouterOS;

use RouterOS\Exceptions\Exception;
use RouterOS\Interfaces\ClientInterface;
use RouterOS\Interfaces\ConfigInterface;
use RouterOS\Interfaces\QueryInterface;

/**
 * Class Client
 * @package RouterOS
 * @since 0.1
 */
class Client implements Interfaces\ClientInterface
{
    /**
     * Socket resource
     * @var resource|null
     */
    private $_socket;

    /**
     * Code of error
     * @var int
     */
    private $_socket_err_num;

    /**
     * Description of socket error
     * @var string
     */
    private $_socket_err_str;

    /**
     * Configuration of connection
     * @var Config
     */
    private $_config;

    /**
     * Client constructor.
     * @param   ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->_config = $config;
        $this->connect();
    }

    /**
     * Get some parameter from config
     *
     * @param   string $parameter
     * @return  mixed
     */
    private function config(string $parameter)
    {
        return $this->_config->get($parameter);
    }

    /**
     * Convert ordinary string to hex string
     *
     * @param   string $string
     * @return  string
     */
    private function encodeLength(string $string): string
    {
        // Yeah, that's insane, but was more ugly, you need read this post if you interesting a details:
        // https://wiki.mikrotik.com/wiki/Manual:API#API_words
        switch (true) {
            case ($string < 0x80):
                $string = \chr($string);
                break;
            case ($string < 0x4000):
                $string |= 0x8000;
                $string = \chr(($string >> 8) & 0xFF)
                    . \chr($string & 0xFF);
                break;
            case ($string < 0x200000):
                $string |= 0xC00000;
                $string = \chr(($string >> 16) & 0xFF)
                    . \chr(($string >> 8) & 0xFF)
                    . \chr($string & 0xFF);
                break;
            case ($string < 0x10000000):
                $string |= 0xE0000000;
                $string = \chr(($string >> 24) & 0xFF)
                    . \chr(($string >> 16) & 0xFF)
                    . \chr(($string >> 8) & 0xFF)
                    . \chr($string & 0xFF);
                break;
            case  ($string >= 0x10000000):
                $string = \chr(0xF0)
                    . \chr(($string >> 24) & 0xFF)
                    . \chr(($string >> 16) & 0xFF)
                    . \chr(($string >> 8) & 0xFF)
                    . \chr($string & 0xFF);
                break;
        }

        return $string;
    }

    /**
     * Send write query to RouterOS (with or without tag)
     *
     * @param   QueryInterface $query
     * @return  ClientInterface
     */
    public function write(QueryInterface $query): ClientInterface
    {
        // Send commands via loop to router
        foreach ($query->getQuery() as $command) {
            $command = trim($command);
            fwrite($this->_socket, $this->encodeLength(\strlen($command)) . $command);
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

        // Not done by default
        $done = false;

        // Read answer from socket in loop
        while (true) {
            // Read the first byte of input which gives us some or all of the length
            // of the remaining reply.
            $byte = \ord(fread($this->_socket, 1));

            // If the first bit is set then we need to remove the first four bits, shift left 8
            // and then read another byte in.
            // We repeat this for the second and third bits.
            // If the fourth bit is set, we need to remove anything left in the first byte
            // and then read in yet another byte.
            if ($byte & 128) {
                if (($byte & 192) === 128) {
                    $length = (($byte & 63) << 8) + \ord(fread($this->_socket, 1));
                } else {
                    if (($byte & 224) === 192) {
                        $length = (($byte & 31) << 8) + \ord(fread($this->_socket, 1));
                        $length = ($length << 8) + \ord(fread($this->_socket, 1));
                    } else {
                        if (($byte & 240) === 224) {
                            $length = (($byte & 15) << 8) + \ord(fread($this->_socket, 1));
                            $length = ($length << 8) + \ord(fread($this->_socket, 1));
                            $length = ($length << 8) + \ord(fread($this->_socket, 1));
                        } else {
                            $length = \ord(fread($this->_socket, 1));
                            $length = ($length << 8) + \ord(fread($this->_socket, 1)) * 3;
                            $length = ($length << 8) + \ord(fread($this->_socket, 1));
                            $length = ($length << 8) + \ord(fread($this->_socket, 1));
                        }
                    }
                }
            } else {
                $length = $byte;
            }

            $_ = '';

            // If we have got more characters to read, read them in.
            if ($length > 0) {
                $_ = '';
                $retlen = 0;
                while ($retlen < $length) {
                    $toread = $length - $retlen;
                    $_ .= fread($this->_socket, $toread);
                    $retlen = \strlen($_);
                }
                $response[] = $_;
            }

            // If we get a !done, make a note of it.
            if ($_ === '!done') {
                $done = true;
            }

            // Get status about latest operation
            $status = stream_get_meta_data($this->_socket);

            // If we do not have unread bytes from socket or <-same and is done, then exit from loop
            if ((!$status['unread_bytes']) || (!$status['unread_bytes'] && $done)) {
                break;
            }
        }

        // Parse results and return
        return $parse ? $this->parseResponse($response) : $response;
    }

    /**
     * Parse response from Router OS
     *
     * @param   array $response Response data
     * @return  array Array with parsed data
     */
    private function parseResponse(array $response): array
    {
        $parsed = [];
        $current = null;
        $single = null;
        foreach ($response as $x) {
            if (\in_array($x, ['!fatal', '!re', '!trap'])) {
                if ($x === '!re') {
                    $current =& $parsed[];
                } else {
                    $current =& $parsed[$x][];
                }
            } elseif ($x !== '!done') {
                $matches = [];
                if (preg_match_all('/[^=]+/', $x, $matches)) {
                    if ($matches[0][0] === 'ret') {
                        $single = $matches[0][1];
                    }
                    $current[$matches[0][0]] = $matches[0][1] ?? '';
                }
            }
        }

        if (empty($parsed) && null !== $single) {
            $parsed[] = $single;
        }

        return $parsed;
    }

    /**
     * Authorization logic
     *
     * @return  bool
     */
    private function login(): bool
    {
        // If legacy login scheme is enabled
        if ($this->config('legacy')) {
            // For the first we need get hash with salt
            $query = new Query('/login');
            $response = $this->write($query)->read();

            // Now need use this hash for authorization
            $query = (new Query('/login'))
                ->add('=name=' . $this->config('user'))
                ->add('=response=00' . md5(\chr(0) . $this->config('pass') . pack('H*', $response[0])));
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
     */
    public function connect(): bool
    {
        // Few attempts in loop
        for ($attempt = 1; $attempt <= $this->config('attempts'); $attempt++) {

            // Initiate socket session
            $this->openSocket();

            // If socket is active
            if ($this->getSocket()) {

                // If we logged in then exit from loop
                if (true === $this->login()) {
                    break;
                }

                // Else close socket and start from begin
                $this->closeSocket();
            }

            // Sleep some time between tries
            sleep($this->config('delay'));
        }

        // Return status of connection
        return true;
    }

    /**
     * Save socket resource to static variable
     *
     * @param   resource|null $socket
     * @return  bool
     */
    private function setSocket($socket): bool
    {
        if (\is_resource($socket)) {
            $this->_socket = $socket;
            return true;
        }
        return false;
    }

    /**
     * Return socket resource if is exist
     *
     * @return  bool|resource
     */
    public function getSocket()
    {
        return \is_resource($this->_socket)
            ? $this->_socket
            : false;
    }

    /**
     * Initiate socket session
     *
     * @return  bool
     */
    private function openSocket(): bool
    {
        // Connect to server
        $socket = false;

        // Default: Context for ssl
        $context = stream_context_create([
            'ssl' => [
                'ciphers' => 'ADH:ALL',
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        // Default: Proto tcp:// but for ssl we need ssl://
        $proto = $this->config('ssl') ? 'ssl://' : '';

        try {
            // Initiate socket client
            $socket = stream_socket_client(
                $proto . $this->config('host') . ':' . $this->config('port'),
                $this->_socket_err_num,
                $this->_socket_err_str,
                $this->config('timeout'),
                STREAM_CLIENT_CONNECT,
                $context
            );
            // Throw error is socket is not initiated
            if (false === $socket) {
                throw new Exception('stream_socket_client() failed: code: ' . $this->_socket_err_num . ' reason:' . $this->_socket_err_str);
            }

        } catch (Exception $e) {
            // __construct
        }

        // Save socket to static variable
        return $this->setSocket($socket);
    }

    /**
     * Close socket session
     *
     * @return bool
     */
    private function closeSocket(): bool
    {
        fclose($this->_socket);
        return true;
    }
}
