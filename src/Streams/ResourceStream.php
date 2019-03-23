<?php

namespace RouterOS\Streams;

use RouterOS\Interfaces\StreamInterface;
use RouterOS\Exceptions\StreamException;

/**
 * class ResourceStream
 *
 * Stream using a resource (socket, file, pipe etc.)
 *
 * @package RouterOS
 * @since   0.9
 */
class ResourceStream implements StreamInterface
{
    protected $stream;

    /**
     * ResourceStream constructor.
     *
     * @param $stream
     */
    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument must be a valid resource type. %s given.',
                    gettype($stream)
                )
            );
        }

        // TODO: Should we verify the resource type?
        $this->stream = $stream;
    }

    /**
     * @param   int $length
     * @return  string
     * @throws  \RouterOS\Exceptions\StreamException
     * @throws  \InvalidArgumentException
     */
    public function read(int $length): string
    {
        if ($length <= 0) {
            throw new \InvalidArgumentException('Cannot read zero ot negative count of bytes from a stream');
        }

        // TODO: Ignore errors here, but why?
        $result = @fread($this->stream, $length);

        if (false === $result) {
            throw new StreamException("Error reading $length bytes");
        }

        return $result;
    }

    /**
     * Writes a string to a stream
     *
     * Write $length bytes of string, if not mentioned, write all the string
     * Must be binary safe (as fread).
     * if $length is greater than string length, write all string and return number of writen bytes
     * if $length os smaller than string length, remaining bytes are losts.
     *
     * @param   string   $string
     * @param   int|null $length the numer of bytes to read
     * @return  int the number of written bytes
     * @throws  \RouterOS\Exceptions\StreamException
     */
    public function write(string $string, int $length = null): int
    {
        if (null === $length) {
            $length = strlen($string);
        }

        // TODO: Ignore errors here, but why?
        $result = @fwrite($this->stream, $string, $length);

        if (false === $result) {
            throw new StreamException("Error writing $length bytes");
        }

        return $result;
    }

    /**
     * Close stream connection
     *
     * @return  void
     * @throws  \RouterOS\Exceptions\StreamException
     */
    public function close()
    {
        $hasBeenClosed = false;

        if (null !== $this->stream) {
            $hasBeenClosed = @fclose($this->stream);
            $this->stream  = null;
        }

        if (false === $hasBeenClosed) {
            throw new StreamException('Error closing stream');
        }
    }
}
