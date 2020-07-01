<?php

namespace RouterOS\Interfaces;

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
     * @return \RouterOS\Interfaces\ConfigInterface
     */
    public function set(string $name, $value): ConfigInterface;

    /**
     * Remove parameter from array by name
     *
     * @param string $parameter
     *
     * @return \RouterOS\Interfaces\ConfigInterface
     */
    public function delete(string $parameter): ConfigInterface;

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
