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
     * Whether to throw exception on stream timeout
     *
     * @var bool
     */
    protected $throwTimeoutException = true;

    /**
     * ResourceStream constructor.
     *
     * @param $stream
     */
    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException(sprintf('Argument must be a valid resource type. %s given.', gettype($stream)));
        }

        // TODO: Should we verify the resource type?
        $this->stream = $stream;
    }

    /**
     * Set whether to throw exception on stream timeout
     *
     * @param bool $throw
     * @return self
     */
    public function setThrowTimeoutException(bool $throw): self
    {
        $this->throwTimeoutException = $throw;
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws StreamException when length parameter is invalid
     * @throws \InvalidArgumentException when the stream have been totally read and read method is called again
     */
    public function read(int $length): string
    {
        if ($length <= 0) {
            throw new \InvalidArgumentException('Cannot read zero ot negative count of bytes from a stream');
        }

        if (!is_resource($this->stream)) {
            throw new StreamException('Stream is not writable');
        }

        $result = '';
        $remaining = $length;

        while ($remaining > 0) {
            $chunk = fread($this->stream, $remaining);

            if (false === $chunk) {
                throw new StreamException("Error reading $length bytes");
            }

            if ('' === $chunk) {
                $info = stream_get_meta_data($this->stream);
                if ($this->throwTimeoutException && !empty($info['timed_out'])) {
                    throw new StreamException('Stream timed out');
                }

                break;
            }

            $result .= $chunk;
            $remaining -= strlen($chunk);

        }

        if (strlen($result) !== $length) {
            throw new StreamException("Stream ended before $length bytes could be read");
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @throws StreamException when not possible to write bytes
     */
    public function write(string $string, ?int $length = null): int
    {
        if (null === $length) {
            $length = strlen($string);
        }

        if (!is_resource($this->stream)) {
            throw new StreamException('Stream is not writable');
        }

        $result = fwrite($this->stream, $string, $length);

        if (false === $result) {
            throw new StreamException("Error writing $length bytes");
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @throws StreamException when not possible to close the stream
     */
    public function close(): void
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
