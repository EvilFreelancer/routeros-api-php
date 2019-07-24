<?php

namespace RouterOS;

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
     * @return \RouterOS\Client
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function w($query): Client
    {
        return $this->write($query);
    }

    /**
     * Alias for ->query() method
     *
     * @param string      $endpoint   Path of API query
     * @param array|null  $where      List of where filters
     * @param string|null $operations Some operations which need make on response
     * @param string|null $tag        Mark query with tag
     * @return \RouterOS\Client
     * @throws \RouterOS\Exceptions\QueryException
     * @since 1.0.0
     */
    public function q(string $endpoint, array $where = null, string $operations = null, string $tag = null): Client
    {
        return $this->query($endpoint, $where, $operations, $tag);
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
     * @return \RouterOS\ResponseIterator
     * @since 1.0.0
     */
    public function ri(): ResponseIterator
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
     * @param string      $endpoint   Path of API query
     * @param array|null  $where      List of where filters
     * @param string|null $operations Some operations which need make on response
     * @param string|null $tag        Mark query with tag
     * @param bool        $parse
     * @return \RouterOS\Client
     * @throws \RouterOS\Exceptions\QueryException
     * @since 1.0.0
     */
    public function qr(string $endpoint, array $where = null, string $operations = null, string $tag = null, bool $parse = true): array
    {
        return $this->query($endpoint, $where, $operations, $tag)->read($parse);
    }

    /**
     * Alias for ->write()->readAsIterator() combination of methods
     *
     * @param string|array|\RouterOS\Query $query
     * @return \RouterOS\ResponseIterator
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\QueryException
     * @since 1.0.0
     */
    public function wri($query): ResponseIterator
    {
        return $this->write($query)->readAsIterator();
    }

    /**
     * Alias for ->write()->read() combination of methods
     *
     * @param string      $endpoint   Path of API query
     * @param array|null  $where      List of where filters
     * @param string|null $operations Some operations which need make on response
     * @param string|null $tag        Mark query with tag
     * @return \RouterOS\Client
     * @throws \RouterOS\Exceptions\QueryException
     * @since 1.0.0
     */
    public function qri(string $endpoint, array $where = null, string $operations = null, string $tag = null): array
    {
        return $this->query($endpoint, $where, $operations, $tag)->readAsIterator();
    }
}
