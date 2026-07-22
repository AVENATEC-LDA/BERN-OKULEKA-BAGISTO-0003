<?php

namespace Webkul\FleetShipping\Listeners;

use Webkul\FleetShipping\Repositories\FleetOrderRepository;
use Webkul\FleetShipping\Services\FleetApiClient;
use Webkul\Sales\Models\Order;

class CancelFleetOrder
{
    public function __construct(
        protected FleetOrderRepository $fleetOrders,
        protected FleetApiClient $fleetApiClient
    ) {
    }

    public function handle(Order $order): void
    {
        if ($order->status !== Order::STATUS_CANCELED) {
            return;
        }

        $fleetOrder = $this->fleetOrders->findByOrderId($order->id);

        if (! $fleetOrder || ! $fleetOrder->fleet_order_id) {
            return;
        }

        if ($fleetOrder->status === 'cancelled' || ! empty($fleetOrder->cancelled_at)) {
            return;
        }

        $response = $this->fleetApiClient->cancelOrder($fleetOrder->fleet_order_id, 'Order canceled in Bagisto');

        if ($response) {
            $fleetOrder->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'last_payload' => $response,
            ]);
        }
    }
}
