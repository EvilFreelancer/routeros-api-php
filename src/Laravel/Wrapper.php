<?php

namespace RouterOS\Laravel;

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Interfaces\ClientInterface;
use RouterOS\Interfaces\ConfigInterface;

class Wrapper
{
    /**
     * Alias for \RouterOS::client() method
     *
     * @param array $params
     *
     * @return \RouterOS\Client
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     * @throws \RouterOS\Exceptions\QueryException
     * @deprecated
     * @codeCoverageIgnore
     */
    public function getClient(array $params = []): ClientInterface
    {
        return $this->client($params);
    }

    /**
     * Get configs of library
     *
     * @param array $params
     *
     * @return \RouterOS\Interfaces\ConfigInterface
     * @throws \RouterOS\Exceptions\ConfigException
     */
    public function config(array $params = []): ConfigInterface
    {
        $config = config('routeros-api');
        $config = array_merge($config, $params);
        $config = new Config($config);

        return $config;
    }

    /**
     * Instantiate client object
     *
     * @param array $params
     * @param bool  $autoConnect
     *
     * @return \RouterOS\Interfaces\ClientInterface
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function client(array $params = [], bool $autoConnect = true): ClientInterface
    {
        $config = $this->config($params);

        return new Client($config, $autoConnect);
    }
}
