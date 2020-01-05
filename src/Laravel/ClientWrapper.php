<?php

namespace RouterOS\Laravel;

use RouterOS\Client;

class ClientWrapper
{
    /**
     * @param array $params
     *
     * @return \RouterOS\Client
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function getClient(array $params = []): Client
    {
        $configs = config('routeros-api');
        $configs = array_merge($configs, $params);

        return new Client($configs);
    }
}