<?php

namespace Webkul\FleetShipping\Services;

use Illuminate\Support\Facades\Http;

/**
 * Cliente HTTP fino para a Fleet Merchant API.
 *
 * Cobre apenas os endpoints documentados em https://developer.fleet.ao :
 *   POST /v1/merchant/quotes
 *   POST /v1/merchant/orders
 *   POST /v1/merchant/orders/{id}/cancel
 */
class FleetApiClient
{
    protected string $baseUrl;

    protected string $apiKey;

    public function __construct()
    {
        $environment = core()->getConfigData('sales.carriers.fleet.environment') ?? 'sandbox';

        $this->baseUrl = $environment === 'production'
            ? 'https://api.fleet.ao/v1/merchant'
            : 'https://sandbox-api.fleet.ao/v1/merchant';

        $this->apiKey = (string) core()->getConfigData('sales.carriers.fleet.api_key');
    }

    protected function client()
    {
        return Http::withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->timeout(8)
            ->retry(2, 300, function ($exception, $request) {
                // Conforme a doc de Errors: só reintentar em timeout de rede, 429, 500, 503.
                // Nunca reintentar em 4xx de validação/estado.
                if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
                    return true;
                }

                $status = method_exists($exception, 'response')
                    ? optional($exception->response)->status()
                    : null;

                return in_array($status, [429, 500, 503], true);
            });
    }

    /**
     * POST /quotes
     * Retorna o objeto `data` da resposta, ou null se a cotação falhar
     * (ex: outside_service_zone, parcel_too_heavy, no_matrix_entry).
     */
    public function quote(array $origin, array $destination, array $parcel): ?array
    {
        $response = $this->client()->post('/quotes', [
            'origin'      => $origin,
            'destination' => $destination,
            'parcel'      => $parcel,
        ]);

        if ($response->failed()) {
            logger()->warning('Fleet quote failed', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);

            return null;
        }

        return $response->json('data');
    }

    /**
     * POST /orders
     * $payload deve seguir exatamente o schema OrderRequest.order da doc.
     * Requer Idempotency-Key único por tentativa lógica de criação.
     */
    public function createOrder(string $idempotencyKey, array $payload): ?array
    {
        $response = $this->client()
            ->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->post('/orders', ['order' => $payload]);

        if ($response->failed()) {
            logger()->error('Fleet order creation failed', [
                'status'           => $response->status(),
                'body'             => $response->json(),
                'idempotency_key'  => $idempotencyKey,
            ]);

            return null;
        }

        return $response->json('data');
    }

    /**
     * POST /orders/{id}/cancel
     */
    public function cancelOrder(string $fleetOrderId, string $reason = ''): ?array
    {
        $response = $this->client()->post("/orders/{$fleetOrderId}/cancel", [
            'reason' => $reason,
        ]);

        return $response->successful() ? $response->json('data') : null;
    }
}
