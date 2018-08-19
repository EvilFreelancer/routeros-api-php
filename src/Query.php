<?php

namespace RouterOS;

class Query
{

    /**
     * Array of query attributes
     * @var array
     */
    private $_attributes = [];

    /**
     * Endpoint of query
     * @var string
     */
    private $_endpoint;

    /**
     * Query constructor.
     *
     * @param   string $endpoint Path of endpoint
     */
    public function __construct(string $endpoint)
    {
        $this->_endpoint = $endpoint;
    }

    /**
     * Append to array yet another attribute of query
     *
     * @param   string $word
     * @return  $this
     */
    public function add(string $word): self
    {
        $this->_attributes[] = $word;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->_attributes;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->_endpoint;
    }

    /**
     * Build body of query
     *
     * @return  array
     */
    public function getQuery(): array
    {
        $endpoint = $this->getEndpoint();
        $attributes = $this->getAttributes();
        array_unshift($attributes, $endpoint);

        return $attributes;
    }
}
