<?php

uses(Tests\TestCase::class);

it('converts kwanza aoa amounts to usd for paypal when configured', function () {
    $payment = new class extends \Webkul\Paypal\Payment\SmartButton
    {
        public function getConfigData($field)
        {
            return match ($field) {
                'kwanza_aoa_to_usd_rate' => 1000,
                default => null,
            };
        }
    };

    expect($payment->getPaypalCurrencyCode('AOA'))->toBe('USD')
        ->and($payment->convertAmountToPayPalCurrency(1000000, 'AOA'))->toBe(1000.0);
});
