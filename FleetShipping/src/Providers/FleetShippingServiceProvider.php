<?php

namespace Webkul\FleetShipping\Providers;

use Illuminate\Support\ServiceProvider;

class FleetShippingServiceProvider extends ServiceProvider
{
    /**
     * Regista os bindings e configurações do pacote.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/Config/carriers.php', 'carriers');
        $this->mergeConfigFrom(dirname(__DIR__) . '/Config/system.php', 'core');
    }

    /**
     * Bootstrap: migrations e rotas do webhook.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__) . '/Database/Migrations');
        $this->loadRoutesFrom(dirname(__DIR__) . '/Routes/web.php');

        $this->app->register(EventServiceProvider::class);
    }
}
