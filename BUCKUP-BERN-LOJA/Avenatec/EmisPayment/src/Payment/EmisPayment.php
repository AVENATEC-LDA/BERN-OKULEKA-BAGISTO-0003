<?php

namespace Avenatec\EmisPayment\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class EmisPayment extends Payment
{
    public const ENDPOINT_DEFAULT = 'https://pagamentonline.emis.co.ao/online-payment-gateway/portal/frameToken';

    public const FRAME_HOST_DEFAULT = 'https://pagamentonline.emis.co.ao/online-payment-gateway/webframe/frame?token=';

    protected $code = 'emis_payment';

    public function getRedirectUrl(): string
    {
        return route('emis_payment.redirect');
    }

    public function getAdditionalDetails(): array
    {
        return [
            'title'       => $this->getConfigData('title'),
            'description' => $this->getConfigData('description'),
        ];
    }

    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : asset('payment-methods/multicaixa-express.png');
    }

    public function getSortOrder()
    {
        return $this->getConfigData('sort_order') ?: parent::getSortOrder();
    }

    public function getConfigData($field)
    {
        $adminValue = parent::getConfigData($field);

        if ($adminValue !== null && $adminValue !== '') {
            return $adminValue;
        }

        return config('emis_payment.'.$field);
    }

    public function isAvailable()
    {
        return parent::isAvailable()
            && $this->getConfigData('reference_prefix')
            && $this->getConfigData('frame_token')
            && $this->getConfigData('terminal_id');
    }

    public function requestFrameToken(int $orderId, float $amount, string $callbackUrl): array
    {
        $endpoint = $this->getConfigData('endpoint') ?: self::ENDPOINT_DEFAULT;
        $frameToken = (string) $this->getConfigData('frame_token');

        $payload = [
            'reference'   => $this->buildReference($orderId),
            'amount'      => $this->toAoa($amount),
            'token'       => $frameToken,
            'mobile'      => $this->getConfigData('mobile_mode') ?: 'PAYMENT',
            'qrCode'      => $this->getConfigData('qrcode_mode') ?: 'PAYMENT',
            'card'        => $this->getConfigData('card_mode') ?: 'DISABLED',
            'callbackUrl' => $callbackUrl,
            'terminal'    => (string) $this->getConfigData('terminal_id'),
        ];

        $this->logEmis('info', '[EMIS][ETAPA_2] Payload enviado a EMIS.', [
            'payload'  => $this->maskPayload($payload),
            'endpoint' => $endpoint,
        ]);

        $response = Http::acceptJson()
            ->asJson()
            ->timeout(45)
            ->post($endpoint, $payload);

        $body = $response->json() ?: [];

        $this->logEmis('info', '[EMIS][ETAPA_2] Resposta da EMIS.', [
            'http_code' => $response->status(),
            'body'      => $this->maskPayload($body),
        ]);

        if (! $response->successful() || $this->extractFrameToken($body) === '') {
            $message = $body['message'] ?? $body['error'] ?? 'Resposta invalida da EMIS';

            $this->logEmis('error', '[EMIS][ETAPA_2] EMIS recusou token.', [
                'message' => $message,
            ]);

            throw new \RuntimeException('EMIS: '.$message);
        }

        return $body;
    }

    public function extractFrameToken(array $response): string
    {
        $frameToken = $response['id']
            ?? $response['frameToken']
            ?? $response['token']
            ?? $response['frameId']
            ?? $response['url']
            ?? $response['frameUrl']
            ?? $response['redirectUrl']
            ?? '';

        return trim((string) $frameToken);
    }

    public function buildFrameUrl(string $frameToken): string
    {
        $frameToken = trim($frameToken);

        if (filter_var($frameToken, FILTER_VALIDATE_URL)) {
            return $frameToken;
        }

        $frameHost = trim((string) ($this->getConfigData('frame_host') ?: self::FRAME_HOST_DEFAULT));

        if (str_contains($frameHost, '{token}')) {
            return str_replace('{token}', rawurlencode($frameToken), $frameHost);
        }

        if (str_ends_with($frameHost, '=') || str_ends_with($frameHost, '/')) {
            return $frameHost.rawurlencode($frameToken);
        }

        $separator = str_contains($frameHost, '?') ? '&' : '?';

        return $frameHost.$separator.'token='.rawurlencode($frameToken);
    }

    public function getWebhookUrl(): string
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl && ! str_contains($appUrl, 'CHANGE_ME')) {
            return $appUrl.'/emis-payment/webhook';
        }

        return route('emis_payment.webhook');
    }

    public function buildReference(int $orderId, ?string $prefix = null): string
    {
        $referencePrefix = $prefix ?? $this->getReferencePrefix();
        $reference = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($referencePrefix).$orderId);

        return substr($reference, 0, 15);
    }

    public function getReferencePrefix(): string
    {
        $prefix = preg_replace('/[^A-Za-z0-9]/', '', strtoupper((string) $this->getConfigData('reference_prefix')));

        return substr($prefix ?: 'EMIS', 0, 6);
    }

    public function toAoa(float $amount): int
    {
        return (int) round($amount);
    }

    public function resolveOrderStatus(string $emisStatus): ?string
    {
        $status = strtoupper($emisStatus);

        if (in_array($status, ['ACCEPTED', 'APPROVED', 'SUCCESS', 'SUCCESSFUL', 'PAID', 'COMPLETED'], true)) {
            return 'processing';
        }

        if (in_array($status, ['REJECTED', 'FAILED', 'CANCELLED', 'EXPIRED'], true)) {
            return 'canceled';
        }

        return null;
    }

    public function mask(string $value): string
    {
        $length = strlen($value);

        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 4).str_repeat('*', $length - 8).substr($value, -4);
    }

    public function maskPayload(array $payload): array
    {
        foreach (['token', 'frame_token', 'id'] as $key) {
            if (! empty($payload[$key]) && is_string($payload[$key])) {
                $payload[$key] = $this->mask($payload[$key]);
            }
        }

        return $payload;
    }

    protected function logEmis(string $level, string $message, array $context = []): void
    {
        Log::channel('single')->log($level, $message, $context);
        Log::channel('stderr')->log($level, $message, $context);
    }
}
