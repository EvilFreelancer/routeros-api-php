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

    public function __construct($stream)
    {
        if (false === is_resource($stream)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument must be a valid resource type. %s given.',
                    gettype($stream)
                )
            );
        }
        // // TODO  : Should we verify the resource type ?
        $this->stream = $stream;
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     */
    public function read(int $length) : string
    {
        if ($length<=0) {
            throw new \InvalidArgumentException("Cannot read zero ot negative count of bytes from a stream");
        }

        $result = @fread($this->stream, $length);

        if (false === $result) {
            throw new StreamException(sprintf("Error reading %d bytes", $length));            
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $string, $length=null) : int
    {
        if (is_null($length)) {
            $length = strlen($string);
        }
        $result = @fwrite($this->stream, $string, $length);
        if (false === $result) {
            throw new StreamException(sprintf("Error writing %d bytes", $length));            
        }
        return $result;
    }

    public function close()
    {
        $hasBeenClosed = false;
        if (!is_null($this->stream)) {
            $hasBeenClosed = @fclose($this->stream);
            $this->stream=null;
        }
        if (false===$hasBeenClosed) {
            throw new StreamException("Error closing stream");
            
        }
    }
}