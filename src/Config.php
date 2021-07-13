<?php

namespace RouterOS;

use RouterOS\Exceptions\ConfigException;
use RouterOS\Helpers\ArrayHelper;
use RouterOS\Helpers\TypeHelper;
use RouterOS\Interfaces\ConfigInterface;
use function gettype;

/**
 * Class Config with array of parameters
 *
 * @package RouterOS
 * @since   0.1
 */
class Config implements ConfigInterface
{
    /**
     * By default legacy login on RouterOS pre-6.43 is not supported
     */
    public const LEGACY = false;

    /**
     * Default port number
     */
    public const PORT = 8728;

    /**
     * Default ssl port number
     */
    public const PORT_SSL = 8729;

    /**
     * If true then use API in SSL mode
     *
     * @see https://wiki.mikrotik.com/wiki/Manual:API-SSL
     */
    public const SSL = false;

    /**
     * List of additional options for work with SSL context
     *
     * @see https://www.php.net/manual/en/context.ssl.php
     */
    public const SSL_OPTIONS = [
        /*
         * Sets the list of available ciphers. By default RouterOS available via 'ADH:ALL'.
         *
         * @example 'ADH:ALL'             // Alias to ADH:ALL@SECLEVEL=1
         *          'ADH:ALL@SECLEVEL=0'  // Everything is permitted. This retains compatibility with previous versions of OpenSSL.
         *          'ADH:ALL@SECLEVEL=1'  // The security level corresponds to a minimum of 80 bits of security.
         *          'ADH:ALL@SECLEVEL=2'  // Security level set to 112 bits of security.
         *          'ADH:ALL@SECLEVEL=3'  // Security level set to 128 bits of security.
         *          'ADH:ALL@SECLEVEL=4'  // Security level set to 192 bits of security.
         *          'ADH:ALL@SECLEVEL=5'  // Security level set to 256 bits of security.
         *
         * @link https://www.openssl.org/docs/man1.1.1/man3/SSL_CTX_set_security_level.html
         */
        'ciphers'           => 'ADH:ALL', // ADH:ALL, ADH:ALL@SECLEVEL=0, ADH:ALL@SECLEVEL=1 ... ADH:ALL@SECLEVEL=5

        // Require verification of SSL certificate used.
        'verify_peer'       => false,

        // Require verification of peer name.
        'verify_peer_name'  => false,

        // Allow self-signed certificates. Requires verify_peer.
        'allow_self_signed' => false,
    ];

    /**
     * Max timeout for answer from router
     */
    public const TIMEOUT = 10;

    /**
     * Count of reconnect attempts
     */
    public const ATTEMPTS = 10;

    /**
     * Delay between attempts in seconds
     */
    public const ATTEMPTS_DELAY = 1;

    /**
     * Delay between attempts in seconds
     */
    public const SSH_PORT = 22;

    /**
     * By default stream on non-blocking mode
     */
    public const BLOCKING = false;

    /**
     * List of allowed parameters of config
     */
    public const ALLOWED = [
        'host'        => 'string',  // Address of Mikrotik RouterOS
        'user'        => 'string',  // Username
        'pass'        => 'string',  // Password
        'port'        => 'integer', // RouterOS API port number for access (if not set use default or default with SSL if SSL enabled)
        'ssl'         => 'boolean', // Enable ssl support (if port is not set this parameter must change default port to ssl port)
        'ssl_options' => 'array', // Enable ssl support (if port is not set this parameter must change default port to ssl port)
        'legacy'      => 'boolean', // Support of legacy login scheme (true - pre 6.43, false - post 6.43)
        'timeout'     => 'integer', // Max timeout for answer from RouterOS
        'attempts'    => 'integer', // Count of attempts to establish TCP session
        'delay'       => 'integer', // Delay between attempts in seconds
        'ssh_port'    => 'integer', // Number of SSH port
        'blocking'    => 'boolean', // Set blocking mode on a stream
    ];

    /**
     * Array of parameters (with some default values)
     *
     * @var array
     */
    private $_parameters = [
        'legacy'      => self::LEGACY,
        'ssl'         => self::SSL,
        'ssl_options' => self::SSL_OPTIONS,
        'timeout'     => self::TIMEOUT,
        'attempts'    => self::ATTEMPTS,
        'delay'       => self::ATTEMPTS_DELAY,
        'ssh_port'    => self::SSH_PORT,
        'blocking'    => self::BLOCKING,
    ];

    /**
     * Config constructor.
     *
     * @param array $parameters List of parameters which can be set on object creation stage
     *
     * @throws \RouterOS\Exceptions\ConfigException
     * @since  0.6
     */
    public function __construct(array $parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RouterOS\Exceptions\ConfigException when name of configuration key is invalid or not allowed
     */
    public function set(string $name, $value): ConfigInterface
    {
        // Check of key in array
        if (ArrayHelper::checkIfKeyNotExist($name, self::ALLOWED)) {
            throw new ConfigException("Requested parameter '$name' not found in list [" . implode(',', array_keys(self::ALLOWED)) . ']');
        }

        // Check what type has this value
        if (TypeHelper::checkIfTypeMismatch(gettype($value), self::ALLOWED[$name])) {
            throw new ConfigException("Parameter '$name' has wrong type '" . gettype($value) . "' but should be '" . self::ALLOWED[$name] . "'");
        }

        // Save value to array
        $this->_parameters[$name] = $value;

        return $this;
    }

    /**
     * Return port number (get from defaults if port is not set by user)
     *
     * @param string $parameter
     *
     * @return bool|int
     */
    private function getPort(string $parameter)
    {
        // If client need port number and port is not set
        if ('port' === $parameter && (!isset($this->_parameters['port']) || null === $this->_parameters['port'])) {
            // then use default with or without ssl encryption
            return (isset($this->_parameters['ssl']) && $this->_parameters['ssl'])
                ? self::PORT_SSL
                : self::PORT;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RouterOS\Exceptions\ConfigException when parameter is not allowed
     */
    public function delete(string $parameter): ConfigInterface
    {
        // Check of key in array
        if (ArrayHelper::checkIfKeyNotExist($parameter, self::ALLOWED)) {
            throw new ConfigException("Requested parameter '$parameter' not found in list [" . implode(',', array_keys(self::ALLOWED)) . ']');
        }

        // Save value to array
        unset($this->_parameters[$parameter]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RouterOS\Exceptions\ConfigException when parameter is not allowed
     */
    public function get(string $parameter)
    {
        // Check of key in array
        if (ArrayHelper::checkIfKeyNotExist($parameter, self::ALLOWED)) {
            throw new ConfigException("Requested parameter '$parameter' not found in list [" . implode(',', array_keys(self::ALLOWED)) . ']');
        }

        return $this->getPort($parameter) ?? $this->_parameters[$parameter];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->_parameters;
    }
}
