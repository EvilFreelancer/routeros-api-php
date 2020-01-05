<?php

namespace RouterOS\Laravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ClientServiceProvide extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../configs/routeros-api.php' => config_path('routeros-api.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../configs/routeros-api.php', 'routeros-api'
        );

        $this->app->bind(ClientWrapper::class);
    }
}
