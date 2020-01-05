<?php

namespace RouterOS\Laravel;

use Illuminate\Support\Facades\Facade;

class ClientFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return ClientWrapper::class;
    }
}
