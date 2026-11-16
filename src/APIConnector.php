<?php

namespace RouterOS;

use RouterOS\Exceptions\StreamException;
use RouterOS\Interfaces\StreamInterface;

/**
 * Class APIConnector
 *
 * Implement middle level dialog with router by masking word dialog implementation to client class
 *
 * @package RouterOS
 * @since   0.9
 */
class APIConnector
{
    /**
     * @var StreamInterface The stream used to communicate with the router
     */
    protected $stream;

    /**
     * APIConnector constructor.
     *
     * @param StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Close stream connection
     *
     * @return void
     */
    public function close(): void
    {
        $this->stream->close();
    }

    /**
     * Reads a WORD from the stream
     *
     * WORDs are part of SENTENCE. Each WORD has to be encoded in certain way - length of the WORD followed by WORD content.
     * Length of the WORD should be given as count of bytes that are going to be sent
     *
     * @return string The word content, en empty string for end of SENTENCE
     */
    public function readWord(): string
    {
        // Get length of next word
        $length = APILengthCoDec::decodeLength($this->stream);
        return ($length > 0) ? $this->stream->read($length) : '';
    }

    /**
     * Write word to stream
     *
     * @param string $word
     *
     * @return int return number of written bytes
     */
    public function writeWord(string $word): int
    {
        $data = APILengthCoDec::encodeLength(strlen($word)) . $word;
        $length = strlen($data);
        $written = 0;

        while ($written < $length) {
            $result = $this->stream->write(substr($data, $written));
            if ($result <= 0) {
                throw new StreamException('Unable to write the complete word to the stream');
            }
            $written += $result;
        }

        return $written;
    }
}
