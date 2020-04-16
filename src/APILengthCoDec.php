<?php

namespace RouterOS;

use DomainException;
use RouterOS\Interfaces\StreamInterface;
use RouterOS\Helpers\BinaryStringHelper;

/**
 * class APILengthCoDec
 *
 * Coder / Decoder for length field in mikrotik API communication protocol
 *
 * @package RouterOS
 * @since   0.9
 */
class APILengthCoDec
{
    /**
     * Encode string to length of string
     *
     *  Encode the length :
    - if length <= 0x7F (binary : 01111111 => 7 bits set to 1)
    - encode length with one byte
    - set the byte to length value, as length maximal value is 7 bits set to 1, the most significant bit is always 0
    - end
    - length <= 0x3FFF (binary : 00111111 11111111 => 14 bits set to 1)
    - encode length with two bytes
    - set length value to 0x8000 (=> 10000000 00000000)
    - add length : as length maximumal value is 14 bits to 1, this does not modify the 2 most significance bits (10)
    - end
    => minimal encoded value is 10000000 10000000
    - length <= 0x1FFFFF (binary : 00011111 11111111 11111111 => 21 bits set to 1)
    - encode length with three bytes
    - set length value to 0xC00000 (binary : 11000000 00000000 00000000)
    - add length : as length maximal value is 21 bits to 1, this does not modify the 3 most significance bits (110)
    - end
    => minimal encoded value is 11000000 01000000 00000000
    - length <= 0x0FFFFFFF (binary : 00001111 11111111 11111111 11111111 => 28 bits set to 1)
    - encode length with four bytes
    - set length value to 0xE0000000 (binary : 11100000 00000000 00000000 00000000)
    - add length : as length maximal value is 28 bits to 1, this does not modify the 4 most significance bits (1110)
    - end
    => minimal encoded value is 11100000 00100000 00000000 00000000
    - length <= 0x7FFFFFFFFF (binary : 00000111 11111111 11111111 11111111 11111111 => 35 bits set to 1)
    - encode length with five bytes
    - set length value to 0xF000000000 (binary : 11110000 00000000 00000000 00000000 00000000)
    - add length : as length maximal value is 35 bits to 1, this does not modify the 5 most significance bits (11110)
    - end
    - length > 0x7FFFFFFFFF : not supported
     *
     * @param   int|float $length
     * @return  string
     */
    public static function encodeLength($length): string
    {
        if ($length < 0) {
            throw new DomainException("Length of word could not to be negative ($length)");
        }

        if ($length <= 0x7F) {
            return BinaryStringHelper::IntegerToNBOBinaryString($length);
        }

        if ($length <= 0x3FFF) {
            return BinaryStringHelper::IntegerToNBOBinaryString(0x8000 + $length);
        }

        if ($length <= 0x1FFFFF) {
            return BinaryStringHelper::IntegerToNBOBinaryString(0xC00000 + $length);
        }

        if ($length <= 0x0FFFFFFF) {
            return BinaryStringHelper::IntegerToNBOBinaryString(0xE0000000 + $length);
        }

        // https://wiki.mikrotik.com/wiki/Manual:API#API_words
        // If len >= 0x10000000 then 0xF0 and len as four bytes
        return BinaryStringHelper::IntegerToNBOBinaryString(0xF000000000 + $length);
    }

    // Decode length of data when reading :
    // The 5 firsts bits of the first byte specify how the length is encoded.
    // The position of the first 0 value bit, starting from the most significant postion. 
    // - 0xxxxxxx => The 7 remainings bits of the first byte is the length : 
    //            => min value of length is 0x00 
    //            => max value of length is 0x7F (127 bytes)
    // - 10xxxxxx => The 6 remainings bits of the first byte plus the next byte represent the lenght
    //            NOTE : the next byte MUST be at least 0x80 !!
    //            => min value of length is 0x80 
    //            => max value of length is 0x3FFF (16,383 bytes, near 16 KB)
    // - 110xxxxx => The 5 remainings bits of th first byte and the two next bytes represent the length
    //             => max value of length is 0x1FFFFF (2,097,151 bytes, near 2 MB)
    // - 1110xxxx => The 4 remainings bits of the first byte and the three next bytes represent the length
    //            => max value of length is 0xFFFFFFF (268,435,455 bytes, near 270 MB)
    // - 11110xxx => The 3 remainings bits of the first byte and the four next bytes represent the length
    //            => max value of length is 0x7FFFFFFF (2,147,483,647 byes, 2GB)
    // - 11111xxx => This byte is not a length-encoded word but a control byte.
    //          =>  Extracted from Mikrotik API doc : 
    //              it is a reserved control byte. 
    //              After receiving unknown control byte API client cannot proceed, because it cannot know how to interpret following bytes
    //              Currently control bytes are not used

    public static function decodeLength(StreamInterface $stream): int
    {
        // if (false === is_resource($stream)) {
        //     throw new \InvalidArgumentException(
        //         sprintf(
        //             'Argument must be a stream resource type. %s given.',
        //             gettype($stream)
        //         )
        //     );
        // }

        // Read first byte
        $firstByte = ord($stream->read(1));

        // If first byte is not set, length is the value of the byte
        if (0 === ($firstByte & 0x80)) {
            return $firstByte;
        }

        // if 10xxxxxx, length is 2 bytes encoded
        if (0x80 === ($firstByte & 0xC0)) {
            // Set 2 most significands bits to 0
            $result = $firstByte & 0x3F;

            // shift left 8 bits to have 2 bytes
            $result <<= 8;

            // read next byte and use it as least significant
            $result |= ord($stream->read(1));
            return $result;
        }

        // if 110xxxxx, length is 3 bytes encoded
        if (0xC0 === ($firstByte & 0xE0)) {
            // Set 3 most significands bits to 0
            $result = $firstByte & 0x1F;

            // shift left 16 bits to have 3 bytes
            $result <<= 16;

            // read next 2 bytes as value and use it as least significant position
            $result |= (ord($stream->read(1)) << 8);
            $result |= ord($stream->read(1));
            return $result;
        }

        // if 1110xxxx, length is 4 bytes encoded
        if (0xE0 === ($firstByte & 0xF0)) {
            // Set 4 most significance bits to 0
            $result = $firstByte & 0x0F;

            // shift left 24 bits to have 4 bytes
            $result <<= 24;

            // read next 3 bytes as value and use it as least significant position
            $result |= (ord($stream->read(1)) << 16);
            $result |= (ord($stream->read(1)) << 8);
            $result |= ord($stream->read(1));
            return $result;
        }

        // if 11110xxx, length is 5 bytes encoded
        if (0xF0 === ($firstByte & 0xF8)) {
            // Not possible on 32 bits systems
            if (PHP_INT_SIZE < 8) {
                // Cannot be done on 32 bits systems
                // PHP5 windows versions of php, even on 64 bits systems was impacted
                // see : https://stackoverflow.com/questions/27865340/php-int-size-returns-4-but-my-operating-system-is-64-bit
                // How can we test it ?

                // @codeCoverageIgnoreStart
                throw new \OverflowException("Your system is using 32 bits integers, cannot decode this value ($firstByte) on this system");
                // @codeCoverageIgnoreEnd
            }

            // Set 5 most significance bits to 0
            $result = $firstByte & 0x07;

            // shift left 232 bits to have 5 bytes
            $result <<= 32;

            // read next 4 bytes as value and use it as least significant position
            $result |= (ord($stream->read(1)) << 24);
            $result |= (ord($stream->read(1)) << 16);
            $result |= (ord($stream->read(1)) << 8);
            $result |= ord($stream->read(1));
            return $result;
        }

        // Now the only solution is 5 most significance bits are set to 1 (11111xxx)
        // This is a control word, not implemented by Mikrotik for the moment 
        throw new \UnexpectedValueException('Control Word found');
    }
}
