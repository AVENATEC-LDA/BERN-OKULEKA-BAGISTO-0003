<?php

namespace Webkul\FleetShipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\FleetShipping\Contracts\FleetOrder as FleetOrderContract;
use Webkul\Sales\Models\OrderProxy;

class FleetOrder extends Model implements FleetOrderContract
{
    protected $table = 'fleet_orders';

    protected $fillable = [
        'order_id',
        'fleet_order_id',
        'external_reference',
        'idempotency_key',
        'tracking_code',
        'tracking_url',
        'status',
        'fee_aoa',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'failed_at',
        'cancelled_at',
        'last_payload',
    ];

    protected $casts = [
        'last_payload' => 'array',
        'assigned_at'  => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at'    => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderProxy::modelClass());
    }
}
