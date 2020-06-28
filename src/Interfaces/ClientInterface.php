<?php

namespace RouterOS\Interfaces;

/**
 * Interface ClientInterface
 *
 * @package RouterOS\Interfaces
 * @since   0.1
 */
interface ClientInterface
{
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
     * @param string|array|\RouterOS\Interfaces\QueryInterface $query
     *
     * @return \RouterOS\Interfaces\ClientInterface
     * @throws \RouterOS\Exceptions\QueryException
     * @deprecated
     */
    public function write($query): ClientInterface;

    /**
     * Send write query to RouterOS (modern version of write)
     *
     * @param string|\RouterOS\Interfaces\QueryInterface $endpoint   Path of API query or Query object
     * @param array|null                                 $where      List of where filters
     * @param string|null                                $operations Some operations which need make on response
     * @param string|null                                $tag        Mark query with tag
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
