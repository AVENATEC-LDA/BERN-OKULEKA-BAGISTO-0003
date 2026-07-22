<?php

namespace Webkul\FleetShipping\Repositories;

use Webkul\Core\Eloquent\Repository;

class FleetQuoteRepository extends Repository
{
    public function model(): string
    {
        return \Webkul\FleetShipping\Models\FleetQuote::class;
    }

    /**
     * Grava (ou substitui) a cotação mais recente para o carrinho.
     */
    public function storeForCart(int $cartId, array $quote, array $origin, array $destination, array $parcel)
    {
        return $this->model->updateOrCreate(
            ['cart_id' => $cartId],
            [
                'quote_id'    => $quote['id'],
                'fee_aoa'     => $quote['fee_aoa'],
                'eta_minutes' => $quote['eta_minutes'] ?? null,
                'distance_km' => $quote['distance_km'] ?? null,
                'valid_until' => $quote['valid_until'],
                'origin'      => $origin,
                'destination' => $destination,
                'parcel'      => $parcel,
                'redeemed'    => false,
            ]
        );
    }

    public function findValidForCart(int $cartId)
    {
        return $this->model
            ->where('cart_id', $cartId)
            ->where('redeemed', false)
            ->where('valid_until', '>', now())
            ->latest()
            ->first();
    }

    public function findLatestForCart(int $cartId)
    {
        return $this->model->where('cart_id', $cartId)->latest()->first();
    }
}
