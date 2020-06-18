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
     * Delay between attempts in seconds
     */
    public const SSH_PORT = 22;

    /**
     * Return socket resource if is exist
     *
     * @return resource
     */
    public function getSocket();

    /**
     * Read answer from server after query was executed
     *
     * A Mikrotik reply is formed of blocks
     * Each block starts with a word, one of ('!re', '!trap', '!done', '!fatal')
     * Each block end with an zero byte (empty line)
     * Reply ends with a complete !done or !fatal block (ended with 'empty line')
     * A !fatal block precedes TCP connexion close
     *
     * @param bool $parse
     *
     * @return mixed
     */
    public function read(bool $parse);

    /**
     * Send write query to RouterOS
     *
     * @param string|array|\RouterOS\Query $query
     *
     * @return \RouterOS\Client
     * @throws \RouterOS\Exceptions\QueryException
     * @deprecated
     */
    public function write($query): Client;

    /**
     * Send write query to RouterOS (modern version of write)
     *
     * @param string|Query $endpoint   Path of API query or Query object
     * @param array|null   $where      List of where filters
     * @param string|null  $operations Some operations which need make on response
     * @param string|null  $tag        Mark query with tag
     *
     * @return \RouterOS\Interfaces\ClientInterface
     * @throws \RouterOS\Exceptions\QueryException
     * @since 1.0.0
     */
    public function query($endpoint, array $where, string $operations, string $tag): ClientInterface;

    /**
     * Execute export command on remote host
     *
     * @return string
     * @throws \RouterOS\Exceptions\ConfigException
     * @throws \RuntimeException
     *
     * @since 1.3.0
     */
    public function export(): string;
}
