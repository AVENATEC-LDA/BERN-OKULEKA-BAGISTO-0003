<?php

namespace Webkul\FleetShipping\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Webkul\FleetShipping\Repositories\FleetOrderRepository;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;

/**
 * Recebe os webhooks assinados da Fleet.
 * Ver docs/FLEET_API_REFERENCE.md > Webhooks para o payload exacto.
 */
class WebhookController extends Controller
{
    public function __construct(
        protected FleetOrderRepository $fleetOrders,
        protected OrderRepository $orderRepository
    ) {
    }

    public function handle(Request $request)
    {
        $signature = $request->header('X-Fleet-Signature', '');
        $secret    = core()->getConfigData('sales.carriers.fleet.webhook_secret');

        if (! $this->verifySignature($request->getContent(), $signature, $secret)) {
            Log::warning('Fleet webhook: assinatura inválida ou ausente.');

            return response()->json(['error' => 'invalid_signature'], 401);
        }

        $payload = $request->json()->all();
        $event   = $payload['event'] ?? null;
        $data    = $payload['data'] ?? [];

        if (! $event || empty($data['order_id'])) {
            return response()->json(['error' => 'invalid_payload'], 400);
        }

        $fleetOrder = $this->fleetOrders->findByFleetOrderId($data['order_id'])
            ?? (! empty($data['external_reference'])
                ? $this->fleetOrders->findByExternalReference($data['external_reference'])
                : null);

        if (! $fleetOrder) {
            Log::warning("Fleet webhook: encomenda não encontrada localmente (order_id={$data['order_id']}).");

            // Devolve 2xx mesmo assim, para a Fleet não continuar a reenviar
            // um evento que não corresponde a nenhum pedido nosso.
            return response()->json(['status' => 'ignored'], 200);
        }

        $this->applyEvent($fleetOrder, $event, $data);

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * HMAC-SHA256 sobre o corpo bruto, conforme docs > Webhooks > Signature verification.
     */
    protected function verifySignature(string $rawBody, string $signatureHeader, ?string $secret): bool
    {
        if (! $secret || ! $signatureHeader) {
            return false;
        }

        $providedHex = str_starts_with($signatureHeader, 'sha256=')
            ? substr($signatureHeader, 7)
            : $signatureHeader;

        $expectedHex = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expectedHex, $providedHex);
    }

    protected function applyEvent($fleetOrder, string $event, array $data): void
    {
        $statusMap = [
            'order.assigned'   => 'assigned',
            'order.picked_up'  => 'picked_up',
            'order.in_transit' => 'in_transit',
            'order.delivered'  => 'delivered',
            'order.failed'     => 'failed',
            'order.cancelled'  => 'cancelled',
        ];

        $newStatus = $statusMap[$event] ?? null;

        if (! $newStatus) {
            return; // evento desconhecido ou não mapeado; ignora sem quebrar
        }

        $lastPayload = $fleetOrder->last_payload ?? [];
        $lastEvent = $lastPayload['event'] ?? null;
        $lastOrderId = $lastPayload['data']['order_id'] ?? null;

        // Idempotência de webhook: ignorar reprocessamento exacto do mesmo evento.
        if ($fleetOrder->status === $newStatus && $lastEvent === $event && $lastOrderId === ($data['order_id'] ?? null)) {
            return;
        }

        $update = ['status' => $newStatus, 'last_payload' => array_merge($data, ['event' => $event])];

        $timestampField = match ($event) {
            'order.assigned'  => 'assigned_at',
            'order.picked_up' => 'picked_up_at',
            'order.delivered' => 'delivered_at',
            'order.failed'    => 'failed_at',
            'order.cancelled' => 'cancelled_at',
            default           => null,
        };

        if ($timestampField) {
            $update[$timestampField] = now();
        }

        $fleetOrder->update($update);

        $bagistoStatus = match ($newStatus) {
            'assigned', 'picked_up', 'in_transit' => Order::STATUS_PROCESSING,
            'delivered' => Order::STATUS_COMPLETED,
            'failed', 'cancelled' => Order::STATUS_CANCELED,
            default => null,
        };

        if ($bagistoStatus) {
            $order = $fleetOrder->order()->first();

            if ($order) {
                $this->orderRepository->updateOrderStatus($order, $bagistoStatus);
            }
        }

        $shipment = $order?->shipments()->where('carrier_code', 'fleet')->first();

        if ($shipment) {
            $shipmentAdditional = array_merge($shipment->additional ?? [], [
                'fleet_status'       => $newStatus,
                'fleet_last_event'   => $event,
                'fleet_tracking_code' => $fleetOrder->tracking_code,
                'fleet_tracking_url'  => $fleetOrder->tracking_url,
            ]);

            $shipment->update([
                'status' => $newStatus,
                'track_number' => $fleetOrder->tracking_code,
                'carrier_code' => 'fleet',
                'carrier_title' => 'Fleet Delivery',
                'additional' => $shipmentAdditional,
            ]);
        }

        $payment = $order?->payment;

        if ($payment) {
            $payment->update([
                'additional' => array_merge($payment->additional ?? [], [
                    'fleet_tracking_code' => $fleetOrder->tracking_code,
                    'fleet_tracking_url'  => $fleetOrder->tracking_url,
                ]),
            ]);
        }
    }
}
