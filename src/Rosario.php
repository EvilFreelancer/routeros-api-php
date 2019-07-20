<?php

namespace RouterOS;

use \Iterator,
    \ArrayAccess,
    \Countable;

/**
 * Class Rosario, created for work with response array from RouterOS
 * as with multiple chunks of values, this class was created by memory save reasons.
 *
 * Based on RouterOSResponseArray solution by @arily
 *
 * @link    https://github.com/arily/RouterOSResponseArray
 * @package RouterOS
 * @since   0.10
 */
class Rosario extends Client implements Iterator, ArrayAccess, Countable
{
    /**
     * List of parsed variables
     *
     * @var array
     */
    protected $parsed = [];

    /**
     * List of RAW variables
     *
     * @var array
     */
    protected $raw = [];

    /**
     * Current position of array
     *
     * @var mixed
     */
    protected $current;

    /**
     * Read answer from server after query was executed
     *
     * A Mikrotik reply is formed of blocks
     * Each block starts with a word, one of ('!re', '!trap', '!done', '!fatal')
     * Each block end with an zero byte (empty line)
     * Reply ends with a complete !done or !fatal block (ended with 'empty line')
     * A !fatal block precedes TCP connexion close
     *
     * @param bool $parse
     * @return array
     */
    public function read(bool $parse = true): array
    {
        // Read answer from original client
        $raw = $orig = parent::read($parse);

        // Refresh current position
        $this->current = 0;

        // This RAW should't be an error
        $position = array_keys($raw, '!re');

        // Split RAW to chinks or use as subarray
        if (isset($position[1])) {
            $length = $position[1] - $position[0];
            $raw    = array_chunk($raw, $length);
            array_pop($raw);
        } else {
            $raw = [$raw];
        }

        // Store parsed RAW data
        $this->raw = $raw;

        // Return ready to use array
        return $orig;
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        ++$this->current;
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        if (isset($this->parsed[$this->current])) {
            return $this->parsed[$this->current];
        }

        if (isset($this->raw[$this->current])) {
            return $this->parseResponse($this->raw[$this->current])[0];
        }

        return false;
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key()
    {
        return $this->current;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->raw[$this->current]);
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->raw);
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        $this->current = 0;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->parsed[] = $value;
        }
        $this->parsed[$offset] = $value;
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->raw[$offset]);
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->parsed[$offset], $this->raw[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (isset($this->parsed[$offset])) {
            return $this->parsed[$offset];
        }

        if (isset($this->raw[$offset])) {
            return $this->parsed[$offset] = $this->parseResponse($this->raw[$offset])[0];
        }

        // For empty() function
        return null;
    }

    /**
     * Cleanup the array
     */
    public function flush()
    {
        $this->raw    = [];
        $this->parsed = [];
    }

}
