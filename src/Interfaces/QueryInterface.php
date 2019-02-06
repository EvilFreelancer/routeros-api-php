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
     * Get endpoint of current query
     *
     * @return  string
     */
    public function getEndpoint(): string;

    /**
     * Build body of query
     *
     * @return  array
     */
    public function getQuery(): array;
}
