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
     * @param bool $parse If need parse output to array
     *
     * @return mixed
     */
    public function read(bool $parse = true);

    /**
     * Send write query to RouterOS (modern version of write)
     *
     * @param array|string|\RouterOS\Interfaces\QueryInterface $endpoint   Path of API query or Query object
     * @param array|null                                       $where      List of where filters
     * @param string|null                                      $operations Some operations which need make on response
     * @param string|null                                      $tag        Mark query with tag
     *
     * @return \RouterOS\Interfaces\ClientInterface
     * @throws \RouterOS\Exceptions\QueryException
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
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
