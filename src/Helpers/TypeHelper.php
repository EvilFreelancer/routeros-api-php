<?php

namespace RouterOS\Helpers;

/**
 * Class TypeHelper
 *
 * @package RouterOS\Helpers
 * @since   0.7
 */
class TypeHelper
{
    /**
     * Compare data types of some value
     *
     * @param   string $name     Name of value
     * @param   mixed  $whatType What type has value
     * @param   mixed  $isType   What type should be
     * @return  bool
     */
    public static function checkIfTypeMismatch(string $name, $whatType, $isType): bool
    {
        return ($whatType !== $isType);
    }
}
