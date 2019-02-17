<?php

namespace RouterOS\Helpers;

/**
 * Class ArrayHelper
 *
 * @package RouterOS\Helpers
 * @since   0.7
 */
class ArrayHelper
{
    /**
     * Check if required single key in array of parameters
     *
     * @param   string $key
     * @param   array  $array
     * @return  bool
     */
    public static function checkIfKeyNotExist(string $key, array $array): bool
    {
        return (!array_key_exists($key, $array) && empty($array[$key]));
    }

    /**
     * Check if required keys in array of parameters
     *
     * @param   array $keys
     * @param   array $array
     * @return  array|bool Return true if all fine, and string with name of key which was not found
     */
    public static function checkIfKeysNotExist(array $keys, array $array)
    {
        $output = [];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array) && empty($array[$key])) {
                $output[] = $key;
            }
        }
        return !empty($output) ? $output : true;
    }
}
