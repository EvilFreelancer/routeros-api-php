<?php

namespace RouterOS\Interfaces;

/**
 * Interface ClientInterface
 * @package RouterOS\Interfaces
 * @since 0.1
 */
interface ClientInterface
{
    /**
     * By default legacy login on RouterOS pre-6.43 is not supported
     */
    const LEGACY = false;

    /**
     * Default port number
     */
    const PORT = 8728;

    /**
     * Default ssl port number
     */
    const PORT_SSL = 8729;

    /**
     * Do not use SSL by default
     */
    const SSL = false;

    /**
     * Max timeout for answer from router
     */
    const TIMEOUT = 10;

    /**
     * Count of reconnect attempts
     */
    const ATTEMPTS = 10;

    /**
     * Delay between attempts in seconds
     */
    const ATTEMPTS_DELAY = 1;

    /**
     * Return socket resource if is exist
     *
     * @return  resource
     */
    public function getSocket();

    /**
     * Read answer from server after query was executed
     *
     * @param   bool $parse
     * @return  array
     */
    public function read(bool $parse = true): array;

    /**
     * Send write query to RouterOS (with or without tag)
     *
     * @param   QueryInterface $query
     * @return  ClientInterface
     */
    public function write(QueryInterface $query): ClientInterface;
}
