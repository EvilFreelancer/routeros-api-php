<?php

namespace RouterOS;

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
     * @var StreamInterface $stream The stream used to communicate with the router
     */
    protected $stream;

    /**
     * Constructor
     *
     * @param StreamInterface $stream
     */

    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
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
     * @param   string $word
     * @return  int return number of written bytes
     */
    public function writeWord(string $word): int
    {
        $encodedLength = APILengthCoDec::encodeLength(strlen($word));
        return $this->stream->write($encodedLength . $word);
    }
}
