<?php

namespace Webkul\FleetShipping\Repositories;

use Webkul\Core\Eloquent\Repository;

class FleetOrderRepository extends Repository
{
    public function model(): string
    {
        return \Webkul\FleetShipping\Models\FleetOrder::class;
    }

    public function findByOrderId(int $orderId)
    {
        return $this->model->where('order_id', $orderId)->first();
    }

    public function findByFleetOrderId(string $fleetOrderId)
    {
        return $this->model->where('fleet_order_id', $fleetOrderId)->first();
    }

    public function findByExternalReference(string $externalReference)
    {
        return $this->model->where('external_reference', $externalReference)->first();
    }
}
