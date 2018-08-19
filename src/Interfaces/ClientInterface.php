<?php

namespace RouterOS\Interfaces;

use RouterOS\Query;

interface ClientInterface
{
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
     * Delay between attempts
     */
    const ATTEMPTS_DELAY = 1;

    /**
     * Return socket resource if is exist
     *
     * @return  bool|resource
     */
    public function getSocket();

    /**
     * Connect to socket server
     *
     * @return  bool
     */
    public function connect(): bool;

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
     * @param   Query $query
     * @param   string|null $tag
     * @return  ClientInterface
     */
    public function write(Query $query, string $tag = null): ClientInterface;

}
