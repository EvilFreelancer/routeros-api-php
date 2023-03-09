<?php

namespace RouterOS;

use Iterator;
use ArrayAccess;
use Countable;
use Serializable;
use function array_keys;
use function array_slice;
use function count;
use function serialize;
use function unserialize;

/**
 * This class was created by memory save reasons, it convert response
 * from RouterOS to readable array in safe way.
 *
 * @param array $raw Array RAW response from server
 *
 * @return mixed
 *
 * Based on RouterOSResponseArray solution by @arily
 *
 * @package RouterOS\Iterators
 * @link    https://github.com/arily/RouterOSResponseArray
 * @since   1.0.0
 */
class ResponseIterator implements Iterator, ArrayAccess, Countable, Serializable
{
    /**
     * List of parser results from array
     *
     * @var array
     */
    private $parsed = [];

    /**
     * List of RAW results from RouterOS
     *
     * @var array
     */
    private $raw;

    /**
     * Initial value of array position
     *
     * @var int
     */
    private $current = 0;

    /**
     * Object of main client
     *
     * @var \RouterOS\Client
     */
    private $client;

    /**
     * ResponseIterator constructor.
     *
     * @param \RouterOS\Client $client
     * @param array            $options Additional options
     */
    public function __construct(Client $client, array $options = [])
    {
        // Set current to default
        $this->rewind();

        // Save client as parameter of object
        $this->client = $client;

        // Read RAW data from client
        $raw = $client->read(false, $options);

        // This RAW shouldn't be an error
        $positions = array_keys($raw, '!re');
        $count     = count($raw);
        $result    = [];

        if (isset($positions[1])) {

            foreach ($positions as $key => $position) {

                // Get length of future block
                $length = isset($positions[$key + 1])
                    ? $positions[$key + 1] - $position + 1
                    : $count - $position;

                // Convert array to simple items, save as result
                $result[] = array_slice($raw, $position, $length);
            }

        } else {
            $result = [$raw];
        }

        $this->raw = $result;
    }

    /**
     * Move forward to next element
     */
    public function next(): void
    {
        ++$this->current;
    }

    /**
     * Previous value
     */
    public function prev(): void
    {
        --$this->current;
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if (isset($this->parsed[$this->current])) {
            return $this->parsed[$this->current];
        }

        if ($this->valid()) {

            if (!isset($this->parsed[$this->current])) {
                $value = $this->client->parseResponse($this->raw[$this->current])[0];
                $this->offsetSet($this->current, $value);
            }

            return $this->parsed[$this->current];
        }

        return null;
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
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
    public function rewind(): void
    {
        $this->current = 0;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->parsed[] = $value;
        } else {
            $this->parsed[$offset] = $value;
        }
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->raw[$offset]) && $this->raw[$offset] !== ['!re'];
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->parsed[$offset], $this->raw[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     *
     * @return bool|mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (isset($this->parsed[$offset])) {
            return $this->parsed[$offset];
        }

        if (isset($this->raw[$offset]) && $this->raw[$offset] !== null) {
            $f = $this->client->parseResponse($this->raw[$offset]);
            if ($f !== []) {
                return $this->parsed[$offset] = $f[0];
            }
        }

        return false;
    }

    /**
     * String representation of object
     *
     * @return string
     */
    public function serialize(): string
    {
        return serialize($this->raw);
    }

    /**
     * Constructs the object
     *
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        $this->raw = unserialize($serialized, null);
    }
}
