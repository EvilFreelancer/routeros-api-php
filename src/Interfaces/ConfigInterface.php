<?php

namespace RouterOS\Interfaces;

use RouterOS\Config;

/**
 * Interface ConfigInterface
 *
 * @package RouterOS\Interfaces
 * @since   0.2
 */
interface ConfigInterface
{
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
    ];

    /**
     * Set parameter into array
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return \RouterOS\Config
     */
    public function set(string $name, $value): Config;

    /**
     * Remove parameter from array by name
     *
     * @param string $parameter
     *
     * @return \RouterOS\Config
     */
    public function delete(string $parameter): Config;

    /**
     * Return parameter of current config by name
     *
     * @param string $parameter
     *
     * @return mixed
     */
    public function get(string $parameter);

    /**
     * Return array with all parameters of configuration
     *
     * @return array
     */
    public function getParameters(): array;
}
