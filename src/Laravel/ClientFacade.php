<?php

namespace RouterOS\Laravel;

use Illuminate\Support\Facades\Facade;

class ClientFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ClientWrapper::class;
    }
}