<?php

namespace RouterOS\Streams;

use RouterOS\Interfaces\StreamInterface;
use RouterOS\Exceptions\StreamException;

/**
 * class StringStream
 *
 * Initialized with a string, the read method retreive it as done with fread, consuming the buffer.
 * When all the string has been read, exception is thrown when try to read again.
 *
 * @package RouterOS\Streams
 * @since   0.9
 */
class StringStream implements StreamInterface
{
    /**
     * @var string $buffer Stores the string to use
     */
    protected $buffer;

    /**
     * StringStream constructor.
     *
     * @param string $string
     */
    public function __construct(string $string)
    {
        $this->buffer = $string;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException when length parameter is invalid
     * @throws StreamException when the stream have been tatly red and read methd is called again
     */
    public function read(int $length): string
    {
        $remaining = strlen($this->buffer);

        if ($length < 0) {
            throw new \InvalidArgumentException('Cannot read a negative count of bytes from a stream');
        }

        if (0 === $remaining) {
            throw new StreamException('End of stream');
        }

        if ($length >= $remaining) {
            // returns all 
            $result = $this->buffer;
            // No more in the buffer
            $this->buffer = '';
        } else {
            // acquire $length characters from the buffer
            $result = substr($this->buffer, 0, $length);
            // remove $length characters from the buffer
            $this->buffer = substr_replace($this->buffer, '', 0, $length);
        }

        return $result;
    }

    /**
     * Fake write method, do nothing except return the "writen" length
     *
     * @param   string   $string The string to write
     * @param   int|null $length the number of characters to write
     * @return  int number of "writen" bytes
     * @throws  \InvalidArgumentException on invalid length
     */
    public function write(string $string, int $length = null): int
    {
        if (null === $length) {
            $length = strlen($string);
        }

        if ($length < 0) {
            throw new \InvalidArgumentException('Cannot write a negative count of bytes');
        }

        return min($length, strlen($string));
    }

    /**
     * Close stream connection
     *
     * @return  void
     */
    public function close()
    {
        $this->buffer = '';
    }
}
