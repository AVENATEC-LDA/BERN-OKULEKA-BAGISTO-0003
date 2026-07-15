<?php

namespace Webkul\FleetShipping\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webkul\FleetShipping\Listeners\DispatchFleetOrder;
use Webkul\FleetShipping\Services\FleetDispatchService;
use Webkul\Sales\Models\Order;

class DispatchFleetOrderTest extends TestCase
{
    public function test_dispatches_fleet_orders_after_processing_status(): void
    {
        $order = new Order();
        $order->status = 'processing';
        $order->shipping_method = 'fleet_fleet';

        $service = $this->createMock(FleetDispatchService::class);
        $service->expects($this->once())
            ->method('dispatch')
            ->with($order);

        $listener = new DispatchFleetOrder($service);
        $listener->handle($order);
    }

    public function test_skips_non_fleet_orders(): void
    {
        $order = new Order();
        $order->status = 'processing';
        $order->shipping_method = 'free_free';

        $service = $this->createMock(FleetDispatchService::class);
        $service->expects($this->never())
            ->method('dispatch');

        $listener = new DispatchFleetOrder($service);
        $listener->handle($order);
    }
}
