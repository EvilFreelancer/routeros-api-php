<?php

namespace RouterOS\Interfaces;

use RouterOS\ResponseIterator;

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
     * @param bool  $parse   If need parse output to array
     * @param array $options If need pass options
     *
     * @return mixed
     */
    public function read(bool $parse = true, array $options = []);

    /**
     * Read using Iterators to improve performance on large dataset
     *
     * @param array $options Additional options
     *
     * @return \RouterOS\ResponseIterator
     * @since 1.0.0
     */
    public function readAsIterator(array $options = []): ResponseIterator;

    /**
     * Send write query to RouterOS (modern version of write)
     *
     * @param array|string|\RouterOS\Interfaces\QueryInterface $endpoint   Path of API query or Query object
     * @param array|null                                       $where      List of where filters
     * @param string|null                                      $operations Some operations which need make on response
     * @param string|null                                      $tag        Mark a query with tag
     *
     * @since 1.0.0
     */
    public function query($endpoint, array $where = null, string $operations = null, string $tag = null): ClientInterface;

    /**
     * Execute export command on remote host, it also will be used
     * if "/export" command passed to query.
     *
     * @param string|null $arguments String with arguments which should be passed to export command
     *
     * @return string
     * @since  1.3.0
     */
    public function export(string $arguments = null): string;
}
