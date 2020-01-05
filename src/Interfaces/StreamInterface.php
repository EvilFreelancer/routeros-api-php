<?php

namespace RouterOS\Interfaces;

/**
 * Interface QueryInterface
 *
 * Stream abstraction
 *
 * @package RouterOS\Interfaces
 * @since   0.9
 */
interface StreamInterface
{
    /**
     * Reads a stream
     *
     * Reads $length bytes from the stream, returns the bytes into a string
     * Must be binary safe (as fread).
     *
     * @param int $length the number of bytes to read
     *
     * @return string a binary string containing the readed byes
     */
    public function read(int $length): string;

    /**
     * Writes a string to a stream
     *
     * Write $length bytes of string, if not mentioned, write all the string
     * Must be binary safe (as fread).
     * if $length is greater than string length, write all string and return number of writen bytes
     * if $length os smaller than string length, remaining bytes are losts.
     *
     * @param string $string
     * @param int    $length the number of bytes to read
     *
     * @return int return number of written bytes
     */
    public function write(string $string, int $length = -1): int;

    /**
     * Close stream connection
     *
     * @return void
     */
    public function close();
}
