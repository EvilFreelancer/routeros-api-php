<?php

namespace RouterOS\Interfaces;

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
     * @param string          $key      Key which need to find
     * @param bool|string|int $value    Value which need to check (by default true)
     * @param bool|string|int $operator It may be one from list [-,=,>,<]
     *
     * @return \RouterOS\Interfaces\QueryInterface
     * @throws \RouterOS\Exceptions\QueryException
     * @since 1.0.0
     */
    public function where(string $key, $operator = '=', $value = null): QueryInterface;

    /**
     * Setter for write/update queries
     *
     * @param string          $key   Key which need to find
     * @param bool|string|int $value Value which need to check (by default true)
     *
     * @return \RouterOS\Interfaces\QueryInterface
     * @throws \RouterOS\Exceptions\QueryException
     * @since 1.1
     */
    public function equal(string $key, $value = null): QueryInterface;

    /**
     * Append additional operations
     *
     * @param string $operations
     *
     * @return \RouterOS\Interfaces\QueryInterface
     *
     * @since 1.0.0
     */
    public function operations(string $operations): QueryInterface;

    /**
     * Append tag to query (it should be at end)
     *
     * @param string $name
     *
     * @return \RouterOS\Interfaces\QueryInterface
     *
     * @since 1.0.0
     */
    public function tag(string $name): QueryInterface;

    /**
     * Append to array yet another attribute of query
     *
     * @param string $word
     *
     * @return \RouterOS\Interfaces\QueryInterface
     */
    public function add(string $word): QueryInterface;

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
     *
     * @return \RouterOS\Interfaces\QueryInterface
     * @since 0.7
     */
    public function setAttributes(array $attributes): QueryInterface;

    /**
     * Get endpoint of current query
     *
     * @return string|null
     */
    public function getEndpoint(): ?string;

    /**
     * Set endpoint of query
     *
     * @param string $endpoint
     *
     * @return \RouterOS\Interfaces\QueryInterface
     * @since 0.7
     */
    public function setEndpoint(string $endpoint): QueryInterface;

    /**
     * Build body of query
     *
     * @return array
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function getQuery(): array;
}
