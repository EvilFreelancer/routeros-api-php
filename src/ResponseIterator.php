<?php

namespace RouterOS;

/**
 * This class was created by memory save reasons, it convert response
 * from RouterOS to readable array in safe way.
 *
 * @param array $raw Array RAW response from server
 * @return mixed
 *
 * Based on RouterOSResponseArray solution by @arily
 *
 * @package RouterOS\Iterators
 * @link    https://github.com/arily/RouterOSResponseArray
 * @since   1.0.0
 */
class ResponseIterator implements \Iterator, \ArrayAccess, \Countable
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
    private $raw = [];

    /**
     * Initial value of array position
     *
     * @var int
     */
    private $current;

    /**
     * Object of main client
     *
     * @var \RouterOS\Client
     */
    private $client;

    /**
     * ResponseIterator constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        // Set current to default
        $this->rewind();

        // Save client as parameter of object
        $this->client = $client;

        // Read RAW data from client
        $raw = $client->read(false);

        // This RAW should't be an error
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
            return $this->client->parseResponse($this->raw[$this->current])[0];
        }

        return null;
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
        } else {
            $this->parsed[$offset] = $value;
        }
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
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
    public function offsetUnset($offset)
    {
        unset($this->parsed[$offset], $this->raw[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     * @return bool|mixed
     */
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
}
