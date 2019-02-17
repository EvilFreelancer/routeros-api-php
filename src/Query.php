<?php

namespace RouterOS;

use RouterOS\Exceptions\QueryException;
use RouterOS\Interfaces\QueryInterface;

/**
 * Class Query for building queries
 *
 * @package RouterOS
 * @since   0.1
 */
class Query implements QueryInterface
{
    /**
     * Array of query attributes
     *
     * @var array
     */
    private $_attributes = [];

    /**
     * Endpoint of query
     *
     * @var string
     */
    private $_endpoint;

    /**
     * Query constructor.
     *
     * @param   array|string $endpoint   Path of endpoint
     * @param   array        $attributes List of attributes which should be set
     * @throws  QueryException
     */
    public function __construct($endpoint, array $attributes = [])
    {
        if (\is_string($endpoint)) {
            $this->setEndpoint($endpoint);
            $this->setAttributes($attributes);
        } elseif (\is_array($endpoint)) {
            $query = array_shift($endpoint);
            $this->setEndpoint($query);
            $this->setAttributes($endpoint);
        } else {
            throw new QueryException('Specified endpoint is not correct');
        }
    }

    /**
     * Append to array yet another attribute of query
     *
     * @param   string $word
     * @return  \RouterOS\Query
     */
    public function add(string $word): Query
    {
        $this->_attributes[] = $word;
        return $this;
    }

    /**
     * Get attributes array of current query
     *
     * @return  array
     */
    public function getAttributes(): array
    {
        return $this->_attributes;
    }

    /**
     * Set array of attributes
     *
     * @param   array $attributes
     * @since   0.7
     * @return  \RouterOS\Query
     */
    public function setAttributes(array $attributes): Query
    {
        $this->_attributes = $attributes;
        return $this;
    }

    /**
     * Get endpoint of current query
     *
     * @return  string|null
     */
    public function getEndpoint()
    {
        return $this->_endpoint;
    }

    /**
     * Set endpoint of query
     *
     * @param   string|null $endpoint
     * @since   0.7
     * @return  \RouterOS\Query
     */
    public function setEndpoint(string $endpoint = null): Query
    {
        $this->_endpoint = $endpoint;
        return $this;
    }

    /**
     * Build body of query
     *
     * @return  array
     * @throws  \RouterOS\Exceptions\QueryException
     */
    public function getQuery(): array
    {
        if ($this->getEndpoint() === null) {
            throw new QueryException('Endpoint of query is not set');
        }

        $endpoint   = $this->getEndpoint();
        $attributes = $this->getAttributes();
        array_unshift($attributes, $endpoint);

        return $attributes;
    }
}
