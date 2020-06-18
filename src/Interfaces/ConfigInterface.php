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
