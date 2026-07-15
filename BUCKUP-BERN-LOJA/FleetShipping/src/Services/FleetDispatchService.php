<?php

namespace Webkul\FleetShipping\Services;

use Illuminate\Support\Str;
use Webkul\Sales\Models\Order;
use Webkul\FleetShipping\Repositories\FleetQuoteRepository;
use Webkul\FleetShipping\Repositories\FleetOrderRepository;

/**
 * Responsável por criar a encomenda na Fleet DEPOIS do pagamento confirmado.
 *
 * Chamar app(FleetDispatchService::class)->dispatch($order) a partir do
 * ponto do teu fluxo onde o pedido passa a "pago" (ex: dentro do handler
 * de webhook do AVEPAY-EMIS / AppyPay, depois de o status mudar).
 */
class FleetDispatchService
{
    public function __construct(
        protected FleetApiClient $api,
        protected FleetQuoteRepository $quotes,
        protected FleetOrderRepository $fleetOrders
    ) {
    }

    public function dispatch(Order $order): void
    {
        // Idempotência: nunca despachar duas vezes o mesmo pedido Bagisto.
        if ($this->fleetOrders->findByOrderId($order->id)) {
            return;
        }

        $quoteRecord = $this->quotes->findValidForCart($order->cart_id)
            ?? $this->quotes->findLatestForCart($order->cart_id);

        if (! $quoteRecord) {
            $this->markFailed($order, 'Nenhuma cotação Fleet encontrada para este carrinho.');
            return;
        }

        $quoteId = $quoteRecord->quote_id;
        $feeAoa  = $quoteRecord->fee_aoa;

        // Re-cotar se a quote expirou entre o checkout e a confirmação do pagamento
        // (ver Quotes > Quote lifecycle: "Expired" -> pedir nova quote).
        if (now()->greaterThan($quoteRecord->valid_until)) {
            $fresh = $this->api->quote($quoteRecord->origin, $quoteRecord->destination, $quoteRecord->parcel);

            if (! $fresh) {
                $this->markFailed($order, 'Falha ao re-cotar após expiração da quote original.');
                return;
            }

            $quoteId = $fresh['id'];
            $feeAoa  = $fresh['fee_aoa'];
        }

        $shippingAddress = $order->shipping_address;

        $destination = $quoteRecord->destination;
        $destination['contact'] = [
            'name'  => trim($shippingAddress->first_name . ' ' . $shippingAddress->last_name),
            'phone' => $shippingAddress->phone,
        ];

        $origin = $quoteRecord->origin;
        $origin['contact'] = [
            'name'  => core()->getConfigData('sales.carriers.fleet.origin_contact_name'),
            'phone' => core()->getConfigData('sales.carriers.fleet.origin_contact_phone'),
        ];

        $items = $order->items->map(fn ($item) => [
            'name'           => $item->name,
            'quantity'       => (int) $item->qty_ordered,
            'sku'            => $item->sku,
            'description'    => $item->name,
            'unit_price_aoa' => (int) $item->price,
        ])->values()->toArray();

        $idempotencyKey = (string) Str::uuid();

        // Regista o registo local ANTES de chamar a API, para permitir
        // retry seguro com a mesma Idempotency-Key em caso de timeout.
        $fleetOrder = $this->fleetOrders->create([
            'order_id'           => $order->id,
            'external_reference' => $order->increment_id,
            'idempotency_key'    => $idempotencyKey,
            'status'             => 'pending_dispatch',
            'fee_aoa'            => $feeAoa,
        ]);

        $response = $this->api->createOrder($idempotencyKey, [
            'external_reference' => $order->increment_id,
            'quote_id'           => $quoteId,
            'origin'             => $origin,
            'destination'        => $destination,
            'parcel'             => $quoteRecord->parcel,
            'items'              => $items,
            'payment_method'     => 'prepaid',
        ]);

        if (! $response) {
            $fleetOrder->update(['status' => 'dispatch_failed']);
            // TODO: notificar operador (email / Chatwoot) para despacho manual.
            return;
        }

        $fleetOrder->update([
            'fleet_order_id' => $response['id'],
            'tracking_code'  => $response['tracking_code'],
            'tracking_url'   => $response['tracking_url'],
            'status'         => 'dispatched',
            'last_payload'   => $response,
        ]);

        $this->quotes->model()->where('id', $quoteRecord->id)->update(['redeemed' => true]);
    }

    protected function markFailed(Order $order, string $reason): void
    {
        logger()->error("Fleet dispatch failed for order #{$order->increment_id}: {$reason}");
        // TODO: notificar operador para despacho manual.
    }
}
