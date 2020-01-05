<?php

namespace RouterOS\Interfaces;

use RouterOS\Client;
use RouterOS\Query;

/**
 * Interface ClientInterface
 *
 * @package RouterOS\Interfaces
 * @since   0.1
 */
interface ClientInterface
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
     * Return socket resource if is exist
     *
     * @return resource
     */
    public function getSocket();

    /**
     * Read answer from server after query was executed
     *
     * @param bool $parse
     * @return mixed
     */
    public function read(bool $parse);

    /**
     * Send write query to RouterOS
     *
     * @param string|array|\RouterOS\Query $query
     * @return \RouterOS\Client
     */
    public function write($query): Client;

    /**
     * Send write query to RouterOS (modern version of write)
     *
     * @param string|Query $endpoint   Path of API query or Query object
     * @param array|null   $where      List of where filters
     * @param string|null  $operations Some operations which need make on response
     * @param string|null  $tag        Mark query with tag
     * @return \RouterOS\Client
     * @throws \RouterOS\Exceptions\QueryException
     * @since 1.0.0
     */
    public function query($endpoint, array $where, string $operations, string $tag): Client;
}
