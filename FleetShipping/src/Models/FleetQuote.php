<?php

namespace Webkul\FleetShipping\Models;

use Illuminate\Database\Eloquent\Model;

class FleetQuote extends Model
{
    protected $table = 'fleet_quotes';

    protected $fillable = [
        'cart_id',
        'quote_id',
        'fee_aoa',
        'eta_minutes',
        'distance_km',
        'valid_until',
        'origin',
        'destination',
        'parcel',
        'redeemed',
    ];

    protected $casts = [
        'origin'      => 'array',
        'destination' => 'array',
        'parcel'      => 'array',
        'valid_until' => 'datetime',
        'redeemed'    => 'boolean',
    ];
}
