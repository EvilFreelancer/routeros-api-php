<?php

namespace RouterOS\Interfaces;

/**
 * Interface QueryInterface
 * @package RouterOS\Interfaces
 * @since 0.2
 */
interface QueryInterface
{
    /**
     * Append to array yet another attribute of query
     *
     * @param   string $word
     * @return  QueryInterface
     */
    public function add(string $word): QueryInterface;

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
