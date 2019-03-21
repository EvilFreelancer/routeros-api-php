<?php
namespace RouterOS\Helpers;

/**
 * class BinaryStringHelper
 * 
 * Strings and binary datas manipulations
 *
 * @package RouterOS\Helpers
 * @since   0.9
 */

class BinaryStringHelper
{
    /**
     * Convert an integer value in a "Network Byte Ordered" binary string (most significant value first)
     *
     * Reads the integer, starting from the most signficant byte, one byte a time.
     * Once reach a non 0 byte, construct a binary string representing this values
     * ex : 
     *   0xFF7 => chr(0x0F).chr(0xF7)
     *   0x12345678 => chr(0x12).chr(0x34).chr(0x56).chr(0x76)
     * Compatible with 8, 16, 32, 64 etc.. bits systems
     *
     * @see https://en.wikipedia.org/wiki/Endianness
     * @param int $value the integer value to be converted
     * @return string the binary string
     */ 
    public static function IntegerToNBOBinaryString(int $value)
    {
        // Initialize an empty string
        $buffer = '';
        // Lets start from the most significant byte
        for ($i=(PHP_INT_SIZE-1); $i>=0; $i--) {
            // Prepare a mask to keep only the most significant byte of $value
            $mask = 0xFF << ($i*8);
            // If the most significant byte is not 0, the final string must contain it
            // If we have already started to construct the string (i.e. there are more signficant digits)
            //   we must set the byte, even if it is a 0.
            //   0xFF00FF, for example, require to set the second byte byte with a 0 value
            if (($value & $mask) || strlen($buffer)!=0) {
                // Get the curent byte by shifting it to least significant position and add it to the string
                // 0xFF12345678 => 0xFF
                $byte = $value>>(8*$i);
                $buffer .= chr($byte);
                // Set the most significant byte to 0 so we can restart the process being shure
                // that the value is left padded with 0
                // 0xFF12345678 => 0x12345678
                // -1 = 0xFFFFF.... (number of F depend of PHP_INT_SIZE )
                $mask = -1 >> ((PHP_INT_SIZE-$i)*8);
                $value &= $mask;
            }
        }
        // Special case, 0 will not fill the buffer, have to construct it manualy
        if (0==$value) {
            $buffer = chr(0);
        }
        return $buffer;
    }
}