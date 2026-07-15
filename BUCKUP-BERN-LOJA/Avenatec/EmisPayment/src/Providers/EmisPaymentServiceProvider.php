<?php

namespace Avenatec\EmisPayment\Providers;

use Illuminate\Support\ServiceProvider;

class EmisPaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/payment-methods.php', 'payment_methods');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/system.php', 'core');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/emis-payment.php', 'emis_payment');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(dirname(__DIR__).'/Http/routes.php');

        $this->loadViewsFrom(dirname(__DIR__).'/Resources/views', 'emis-payment');

        $this->loadTranslationsFrom(dirname(__DIR__).'/Resources/lang', 'emis-payment');
    }
}
