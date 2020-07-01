<?php

namespace RouterOS;

use RouterOS\Exceptions\QueryException;
use RouterOS\Interfaces\QueryInterface;
use function in_array;
use function is_array;
use function is_string;

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
     * Some additional operations
     *
     * @var string
     */
    private $_operations;

    /**
     * Tag of query
     *
     * @var string
     */
    private $_tag;

    /**
     * Endpoint of query
     *
     * @var string
     */
    private $_endpoint;

    /**
     * List of available operators for "->where()" method
     */
    public const AVAILABLE_OPERATORS = [
        '-',  // Does not have
        '=',  // Equal
        '>',  // More than
        '<'   // Less than
    ];

    /**
     * Query constructor.
     *
     * @param array|string $endpoint   Path of endpoint
     * @param array        $attributes List of attributes which should be set
     *
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function __construct($endpoint, array $attributes = [])
    {
        if (is_string($endpoint)) {
            $this->setEndpoint($endpoint);
            $this->setAttributes($attributes);
        } elseif (is_array($endpoint)) {
            $query = array_shift($endpoint);
            $this->setEndpoint($query);
            $this->setAttributes($endpoint);
        } else {
            throw new QueryException('Specified endpoint is incorrect');
        }
    }

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
    public function where(string $key, $operator = null, $value = null): QueryInterface
    {
        return $this->world('?' . $key, $operator, $value);
    }

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
    public function equal(string $key, $value = null): QueryInterface
    {
        return $this->world('=' . $key, null, $value);
    }

    /**
     * Write world to RouterOS (the work is mine)
     *
     * @param string          $key      Key which need to find
     * @param bool|string|int $value    Value which need to check (by default true)
     * @param bool|string|int $operator It may be one from list [-,=,>,<]
     *
     * @return \RouterOS\Interfaces\QueryInterface
     * @throws \RouterOS\Exceptions\QueryException
     */
    private function world(string $key, $operator = null, $value = null): QueryInterface
    {
        if (null !== $operator && null === $value) {

            // Client may set only two parameters, that mean what $operator is $value
            $value = $operator;

            // And operator should be "="
            $operator = null;
        }

        if (null !== $operator && null !== $value) {
            // If operator is available in list
            if (in_array($operator, self::AVAILABLE_OPERATORS, true)) {
                $key = $operator . $key;
            } else {
                throw new QueryException('Operator "' . $operator . '" in not in allowed list [' . implode(',', self::AVAILABLE_OPERATORS) . ']');
            }
        }

        if (null !== $value) {
            $value = '=' . $value;
        }

        $this->add($key . $value);
        return $this;
    }

    /**
     * Append additional operations
     *
     * @param string $operations
     *
     * @return \RouterOS\Interfaces\QueryInterface
     * @since 1.0.0
     */
    public function operations(string $operations): QueryInterface
    {
        $this->_operations = '?#' . $operations;
        return $this;
    }

    /**
     * Append tag to query (it should be at end)
     *
     * @param string $name
     *
     * @return \RouterOS\Interfaces\QueryInterface
     * @since 1.0.0
     */
    public function tag(string $name): QueryInterface
    {
        $this->_tag = '.tag=' . $name;
        return $this;
    }

    /**
     * Append to array yet another attribute of query
     *
     * @param string $word
     *
     * @return \RouterOS\Interfaces\QueryInterface
     */
    public function add(string $word): QueryInterface
    {
        $this->_attributes[] = $word;
        return $this;
    }

    /**
     * Get attributes array of current query
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->_attributes;
    }

    /**
     * Set array of attributes
     *
     * @param array $attributes
     *
     * @return \RouterOS\Interfaces\QueryInterface
     * @since 0.7
     */
    public function setAttributes(array $attributes): QueryInterface
    {
        $this->_attributes = $attributes;
        return $this;
    }

    /**
     * Get endpoint of current query
     *
     * @return string|null
     */
    public function getEndpoint(): ?string
    {
        return $this->_endpoint;
    }

    /**
     * Set endpoint of query
     *
     * @param string|null $endpoint
     *
     * @return \RouterOS\Interfaces\QueryInterface
     * @since 0.7
     */
    public function setEndpoint(string $endpoint = null): QueryInterface
    {
        $this->_endpoint = $endpoint;
        return $this;
    }

    /**
     * Build body of query
     *
     * @return array
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function getQuery(): array
    {
        if ($this->_endpoint === null) {
            throw new QueryException('Endpoint of query is not set');
        }

        // Get all attributes and prepend endpoint to this list
        $attributes = $this->getAttributes();
        array_unshift($attributes, $this->_endpoint);

        // If operations is set then add to query
        if (is_string($this->_operations) && !empty($this->_operations)) {
            $attributes[] = $this->_operations;
        }

        // If tag is set then added to query
        if (is_string($this->_tag) && !empty($this->_tag)) {
            $attributes[] = $this->_tag;
        }

        return $attributes;
    }
}
