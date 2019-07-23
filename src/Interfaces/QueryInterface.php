<?php

namespace RouterOS\Interfaces;

use RouterOS\Query;

/**
 * Interface QueryInterface
 *
 * @package RouterOS\Interfaces
 * @since   0.2
 */
interface QueryInterface
{
    /**
     * Where logic of query
     *
     * @param string      $key Key which need to find
     * @param bool        $value Value which need to check (by default true)
     * @param string|null $operator It may be one from list [-,=,>,<]
     * @return \RouterOS\Query
     * @throws \RouterOS\Exceptions\ClientException
     * @since 1.0.0
     */
    public function where(string $key, $value = true, string $operator = '');

    /**
     * Append additional operations
     *
     * @param string $operations
     * @since 1.0.0
     */
    public function operations(string $operations);

    /**
     * Append tag to query (it should be at end)
     *
     * @param string $name
     * @since 1.0.0
     */
    public function tag(string $name);

    /**
     * Append to array yet another attribute of query
     *
     * @param string $word
     * @return \RouterOS\Query
     */
    public function add(string $word): Query;

    /**
     * Get attributes array of current query
     *
     * @return array
     */
    public function getAttributes(): array;

    /**
     * Set array of attributes
     *
     * @param array $attributes
     * @return \RouterOS\Query
     * @since 0.7
     */
    public function setAttributes(array $attributes): Query;

    /**
     * Get endpoint of current query
     *
     * @return string|null
     */
    public function getEndpoint();

    /**
     * Set endpoint of query
     *
     * @param string $endpoint
     * @return \RouterOS\Query
     * @since 0.7
     */
    public function setEndpoint(string $endpoint): Query;

    /**
     * Build body of query
     *
     * @return array
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function getQuery(): array;
}
