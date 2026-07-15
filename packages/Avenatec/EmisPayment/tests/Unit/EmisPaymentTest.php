<?php

use Avenatec\EmisPayment\Payment\EmisPayment;

it('builds an emis reference with prefix and order id', function () {
    $payment = new EmisPayment;

    expect($payment->buildReference(1234, 'BERNO'))->toBe('BERNO1234');
});

it('limits emis reference to fifteen alphanumeric characters', function () {
    $payment = new EmisPayment;

    expect($payment->buildReference(9876543210, 'BERNO-STORE'))->toBe('BERNOSTORE98765');
});

it('converts amounts to integer aoa', function () {
    $payment = new EmisPayment;

    expect($payment->toAoa(300.49))->toBe(300)
        ->and($payment->toAoa(300.50))->toBe(301);
});

it('maps emis statuses to bagisto order statuses', function (string $emisStatus, ?string $orderStatus) {
    $payment = new EmisPayment;

    expect($payment->resolveOrderStatus($emisStatus))->toBe($orderStatus);
})->with([
    ['ACCEPTED', 'processing'],
    ['APPROVED', 'processing'],
    ['SUCCESS', 'processing'],
    ['SUCCESSFUL', 'processing'],
    ['PAID', 'processing'],
    ['COMPLETED', 'processing'],
    ['REJECTED', 'canceled'],
    ['FAILED', 'canceled'],
    ['CANCELLED', 'canceled'],
    ['EXPIRED', 'canceled'],
    ['PENDING', null],
]);

it('masks frame tokens', function () {
    $payment = new EmisPayment;

    expect($payment->mask('1234567890abcdef'))->toBe('1234********cdef')
        ->and($payment->mask('12345678'))->toBe('********');
});

it('returns the default multicaixa express checkout logo', function () {
    $payment = new class extends EmisPayment
    {
        public function getConfigData($field)
        {
            return null;
        }
    };

    expect($payment->getImage())->toContain('payment-methods/multicaixa-express.png');
});

it('generates the emis webhook url from app url', function () {
    config(['app.url' => 'https://loja.bernokuleka.com']);

    $payment = new EmisPayment;

    expect($payment->getWebhookUrl())->toBe('https://loja.bernokuleka.com/emis-payment/webhook');
});

it('extracts frame tokens from supported emis response fields', function () {
    $payment = new EmisPayment;

    expect($payment->extractFrameToken(['frameUrl' => 'https://emis.test/frame?id=abc']))->toBe('https://emis.test/frame?id=abc')
        ->and($payment->extractFrameToken(['redirectUrl' => 'https://emis.test/pay/abc']))->toBe('https://emis.test/pay/abc')
        ->and($payment->extractFrameToken(['id' => 'frame-token-123']))->toBe('frame-token-123');
});

it('builds frame urls from tokens and complete urls', function () {
    $payment = new class extends EmisPayment
    {
        public function getConfigData($field)
        {
            return $field === 'frame_host'
                ? 'https://pagamentonline.emis.co.ao/online-payment-gateway/webframe/frame?token='
                : null;
        }
    };

    expect($payment->buildFrameUrl('frame-token-123'))->toBe('https://pagamentonline.emis.co.ao/online-payment-gateway/webframe/frame?token=frame-token-123')
        ->and($payment->buildFrameUrl('https://emis.test/frame?id=abc'))->toBe('https://emis.test/frame?id=abc');
});
