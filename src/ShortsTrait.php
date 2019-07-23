<?php

namespace RouterOS;

use RouterOS\Interfaces\ClientInterface;

/**
 * Trait ShortsTrait
 *
 * All shortcuts was moved to this class
 *
 * @package RouterOS
 * @since   1.0.0
 */
trait ShortsTrait
{
    /**
     * Alias for ->write() method
     *
     * @param string|array|\RouterOS\Query $query
     * @return \RouterOS\Interfaces\ClientInterface
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function w($query): ClientInterface
    {
        return $this->write($query);
    }

    /**
     * Alias for ->read() method
     *
     * @param bool $parse
     * @return mixed
     * @since 0.7
     */
    public function r(bool $parse = true)
    {
        return $this->read($parse);
    }

    /**
     * Alias for ->readAsIterator() method
     *
     * @return mixed
     * @since 0.7
     */
    public function ri()
    {
        return $this->readAsIterator();
    }

    /**
     * Alias for ->write()->read() combination of methods
     *
     * @param string|array|\RouterOS\Query $query
     * @param bool                         $parse
     * @return array
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\QueryException
     * @since 0.6
     */
    public function wr($query, bool $parse = true): array
    {
        return $this->write($query)->read($parse);
    }

    /**
     * Alias for ->write()->read() combination of methods
     *
     * @param string|array|\RouterOS\Query $query
     * @return array
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\QueryException
     * @since 0.6
     */
    public function wri($query): array
    {
        return $this->write($query)->readAsIterator();
    }
}
