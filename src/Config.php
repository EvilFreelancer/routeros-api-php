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
     * Do not use SSL by default
     */
    public const SSL = false;

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
     * List of allowed parameters of config
     */
    public const ALLOWED = [
        // Address of Mikrotik RouterOS
        'host'     => 'string',
        // Username
        'user'     => 'string',
        // Password
        'pass'     => 'string',
        // RouterOS API port number for access (if not set use default or default with SSL if SSL enabled)
        'port'     => 'integer',
        // Enable ssl support (if port is not set this parameter must change default port to ssl port)
        'ssl'      => 'boolean',
        // Support of legacy login scheme (true - pre 6.43, false - post 6.43)
        'legacy'   => 'boolean',
        // Max timeout for answer from RouterOS
        'timeout'  => 'integer',
        // Count of attempts to establish TCP session
        'attempts' => 'integer',
        // Delay between attempts in seconds
        'delay'    => 'integer',
        // Number of SSH port
        'ssh_port' => 'integer',
    ];

    /**
     * Array of parameters (with some default values)
     *
     * @var array
     */
    private $_parameters = [
        'legacy'   => self::LEGACY,
        'ssl'      => self::SSL,
        'timeout'  => self::TIMEOUT,
        'attempts' => self::ATTEMPTS,
        'delay'    => self::ATTEMPTS_DELAY,
        'ssh_port' => self::SSH_PORT,
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
     * Set parameter into array
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return \RouterOS\Config
     * @throws \RouterOS\Exceptions\ConfigException
     */
    public function set(string $name, $value): Config
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
        if ($parameter === 'port' && (!isset($this->_parameters['port']) || null === $this->_parameters['port'])) {
            // then use default with or without ssl encryption
            return (isset($this->_parameters['ssl']) && $this->_parameters['ssl'])
                ? self::PORT_SSL
                : self::PORT;
        }
        return null;
    }

    /**
     * Remove parameter from array by name
     *
     * @param string $name
     *
     * @return \RouterOS\Config
     * @throws \RouterOS\Exceptions\ConfigException
     */
    public function delete(string $name): Config
    {
        // Check of key in array
        if (ArrayHelper::checkIfKeyNotExist($name, self::ALLOWED)) {
            throw new ConfigException("Requested parameter '$name' not found in list [" . implode(',', array_keys(self::ALLOWED)) . ']');
        }

        // Save value to array
        unset($this->_parameters[$name]);

        return $this;
    }

    /**
     * Return parameter of current config by name
     *
     * @param string $name
     *
     * @return mixed
     * @throws \RouterOS\Exceptions\ConfigException
     */
    public function get(string $name)
    {
        // Check of key in array
        if (ArrayHelper::checkIfKeyNotExist($name, self::ALLOWED)) {
            throw new ConfigException("Requested parameter '$name' not found in list [" . implode(',', array_keys(self::ALLOWED)) . ']');
        }

        return $this->getPort($name) ?? $this->_parameters[$name];
    }

    /**
     * Return array with all parameters of configuration
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->_parameters;
    }
}
