<?php

namespace RouterOS\Tests\Laravel;

use RouterOS\Laravel\Facade;
use RouterOS\Laravel\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Class TestCase
 *
 * @package Tests
 */
abstract class TestCase extends Orchestra
{
    /**
     * @inheritdoc
     */
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getPackageAliases($app): array
    {
        return [
            'RouterOS' => Facade::class,
        ];
    }
}
