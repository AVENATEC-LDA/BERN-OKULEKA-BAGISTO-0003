<?php

namespace Webkul\FleetShipping\Listeners;

use Webkul\FleetShipping\Services\FleetDispatchService;
use Webkul\Sales\Models\Order;

class DispatchFleetOrder
{
    public function __construct(protected FleetDispatchService $dispatchService)
    {
    }

    public function handle(Order $order): void
    {
        if ($order->status !== Order::STATUS_PROCESSING) {
            return;
        }

        if (! $order->shipping_method || ! str_starts_with($order->shipping_method, 'fleet_')) {
            return;
        }

        $this->dispatchService->dispatch($order);
    }
}
