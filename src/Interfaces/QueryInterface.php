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
     * Append to array yet another attribute of query
     *
     * @param   string $word
     * @return  Query
     */
    public function add(string $word): Query;

    /**
     * Get attributes array of current query
     *
     * @return  array
     */
    public function getAttributes(): array;

    /**
     * Set array of attributes
     *
     * @param   array $attributes
     * @since   0.7
     * @return  \RouterOS\Query
     */
    public function setAttributes(array $attributes): Query;

    /**
     * Get endpoint of current query
     *
     * @return  string|null
     */
    public function getEndpoint();

    /**
     * Set endpoint of query
     *
     * @param   string $endpoint
     * @since   0.7
     * @return  \RouterOS\Query
     */
    public function setEndpoint(string $endpoint): Query;

    /**
     * Build body of query
     *
     * @return  array
     * @throws  \RouterOS\Exceptions\QueryException
     */
    public function getQuery(): array;
}
