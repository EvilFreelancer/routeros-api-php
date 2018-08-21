<?php

namespace RouterOS;

use RouterOS\Exceptions\Exception;
use RouterOS\Interfaces\ConfigInterface;

/**
 * Class Config
 * @package RouterOS
 * @since 0.1
 */
class Config implements ConfigInterface
{
    /**
     * Array of parameters (with defaults)
     * @var array
     */
    private $_parameters = [
        'legacy' => Client::LEGACY,
        'ssl' => Client::SSL,
        'timeout' => Client::TIMEOUT,
        'attempts' => Client::ATTEMPTS,
        'delay' => Client::ATTEMPTS_DELAY
    ];

    /**
     * Check if key in array
     *
     * @param   string $key
     * @param   array $array
     * @throws  Exception
     */
    private function keyAllowed(string $key, array $array)
    {
        // Check if parameter in list of allowed parameters
        if (!array_key_exists($key, $array)) {
            throw new Exception("Requested parameter \"$key\" not found in allowed list [" . implode(',',
                    array_keys($array)) . ']');
        }
    }

    /**
     * Set parameter into array
     *
     * @param   string $name
     * @param   mixed $value
     * @return  ConfigInterface
     */
    public function set(string $name, $value): ConfigInterface
    {
        try {

            // Check of key in array
            $this->keyAllowed($name, self::ALLOWED);

            // Get type of current variable
            $whatType = \gettype($value);
            // Get allowed type of parameter
            $type = self::ALLOWED[$name];
            $isType = 'is_' . $type;

            // Check what type has this value
            if (!$isType($value)) {
                throw new Exception("Parameter \"$name\" has wrong type \"$whatType\" but should be \"$type\"");
            }

        } catch (Exception $e) {
            // __construct
        }

        // Save value to array
        $this->_parameters[$name] = $value;

        return $this;
    }

    /**
     * Return parameter of current config by name
     *
     * @param   string $parameter
     * @return  mixed
     */
    public function get(string $parameter)
    {
        try {
            // Check of key in array
            $this->keyAllowed($parameter, self::ALLOWED);

        } catch (Exception $e) {
            // __construct
        }

        // If client need port number and port is not set
        if ($parameter === 'port' && !isset($this->_parameters['port'])) {
            // then use default with or without ssl encryption
            return (isset($this->_parameters['ssl']) && $this->_parameters['ssl'])
                ? Client::PORT_SSL
                : Client::PORT;
        }

        return $this->_parameters[$parameter];
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
