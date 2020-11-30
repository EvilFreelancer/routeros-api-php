<?php

namespace RouterOS;

/**
 * Trait ShortsTrait
 *
 * All shortcuts was moved to this class
 *
 * @package RouterOS
 * @since   1.0.0
 * @codeCoverageIgnore
 */
trait ShortsTrait
{
    /**
     * Alias for ->query() method
     *
     * @param array|string|\RouterOS\Interfaces\QueryInterface $endpoint   Path of API query or Query object
     * @param array|null                                       $where      List of where filters
     * @param string|null                                      $operations Some operations which need make on response
     * @param string|null                                      $tag        Mark query with tag
     *
     * @return \RouterOS\Client
     * @since 1.0.0
     */
    public function q($endpoint, array $where = null, string $operations = null, string $tag = null): Client
    {
        return $this->query($endpoint, $where, $operations, $tag);
    }

    /**
     * Alias for ->read() method
     *
     * @param bool  $parse   If need parse output to array
     * @param array $options Additional options
     *
     * @return mixed
     * @since 0.7
     */
    public function r(bool $parse = true, array $options = [])
    {
        return $this->read($parse, $options);
    }

    /**
     * Alias for ->readAsIterator() method
     *
     * @param array $options Additional options
     *
     * @return \RouterOS\ResponseIterator
     * @since 1.0.0
     */
    public function ri(array $options = []): ResponseIterator
    {
        return $this->readAsIterator($options);
    }

    /**
     * Alias for ->query()->read() combination of methods
     *
     * @param array|string|\RouterOS\Interfaces\QueryInterface $endpoint   Path of API query or Query object
     * @param array|null                                       $where      List of where filters
     * @param string|null                                      $operations Some operations which need make on response
     * @param string|null                                      $tag        Mark query with tag
     * @param bool                                             $parse      If need parse output to array
     * @param array                                            $options    Additional options
     *
     * @return array
     * @since 1.0.0
     */
    public function qr($endpoint, array $where = null, string $operations = null, string $tag = null, bool $parse = true, array $options = []): array
    {
        return $this->query($endpoint, $where, $operations, $tag)->read($parse, $options);
    }

    /**
     * Alias for ->query()->readAsIterator() combination of methods
     *
     * @param array|string|\RouterOS\Interfaces\QueryInterface $endpoint   Path of API query or Query object
     * @param array|null                                       $where      List of where filters
     * @param string|null                                      $operations Some operations which need make on response
     * @param string|null                                      $tag        Mark query with tag
     * @param array                                            $options    Additional options
     *
     * @return \RouterOS\ResponseIterator
     * @since 1.0.0
     */
    public function qri($endpoint, array $where = null, string $operations = null, string $tag = null, array $options = []): ResponseIterator
    {
        return $this->query($endpoint, $where, $operations, $tag)->readAsIterator($options);
    }
}
