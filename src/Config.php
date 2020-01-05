<?php

namespace RouterOS;

use RouterOS\Exceptions\ConfigException;
use RouterOS\Helpers\ArrayHelper;
use RouterOS\Helpers\TypeHelper;
use RouterOS\Interfaces\ConfigInterface;

/**
 * Class Config with array of parameters
 *
 * @package RouterOS
 * @since   0.1
 */
class Config implements ConfigInterface
{
    /**
     * Array of parameters (with some default values)
     *
     * @var array
     */
    private $_parameters = [
        'legacy'   => Client::LEGACY,
        'ssl'      => Client::SSL,
        'timeout'  => Client::TIMEOUT,
        'attempts' => Client::ATTEMPTS,
        'delay'    => Client::ATTEMPTS_DELAY
    ];

    /**
     * Config constructor.
     *
     * @param   array $parameters List of parameters which can be set on object creation stage
     * @throws  ConfigException
     * @since   0.6
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
     * @param   string $name
     * @param   mixed  $value
     * @return  \RouterOS\Config
     * @throws  ConfigException
     */
    public function set(string $name, $value): Config
    {
        // Check of key in array
        if (ArrayHelper::checkIfKeyNotExist($name, self::ALLOWED)) {
            throw new ConfigException("Requested parameter '$name' not found in list [" . implode(',', array_keys(self::ALLOWED)) . ']');
        }

        // Check what type has this value
        if (TypeHelper::checkIfTypeMismatch(\gettype($value), self::ALLOWED[$name])) {
            throw new ConfigException("Parameter '$name' has wrong type '" . \gettype($value) . "' but should be '" . self::ALLOWED[$name] . "'");
        }

        // Save value to array
        $this->_parameters[$name] = $value;

        return $this;
    }

    /**
     * Return port number (get from defaults if port is not set by user)
     *
     * @param   string $parameter
     * @return  bool|int
     */
    private function getPort(string $parameter)
    {
        // If client need port number and port is not set
        if ($parameter === 'port' && !isset($this->_parameters['port']) && null !== $this->_parameters['port']) {
            // then use default with or without ssl encryption
            return (isset($this->_parameters['ssl']) && $this->_parameters['ssl'])
                ? Client::PORT_SSL
                : Client::PORT;
        }
        return null;
    }

    /**
     * Remove parameter from array by name
     *
     * @param   string $name
     * @return  \RouterOS\Config
     * @throws  \RouterOS\Exceptions\ConfigException
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
     * @param   string $name
     * @return  mixed
     * @throws  \RouterOS\Exceptions\ConfigException
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
     * @return  array
     */
    public function getParameters(): array
    {
        return $this->_parameters;
    }
}
