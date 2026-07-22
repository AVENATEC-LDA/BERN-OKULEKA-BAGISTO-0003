<?php

namespace Webkul\FleetShipping\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\FleetShipping\Listeners\CancelFleetOrder;
use Webkul\FleetShipping\Listeners\DispatchFleetOrder;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen('sales.order.update-status.after', DispatchFleetOrder::class);
        Event::listen('sales.order.cancel.after', CancelFleetOrder::class);
    }
}
