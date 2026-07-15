<?php

namespace Avenatec\EmisPayment\Tests;

use Tests\TestCase;

class EmisPaymentTestCase extends TestCase {}

function emisFakeOrder(string $status = 'pending', string $method = 'emis_payment'): object
{
    return new class($status, $method)
    {
        public int $id = 1234;

        public int $cart_id = 88;

        public float $grand_total = 300.0;

        public float $base_grand_total = 300.0;

        public string $order_currency_code = 'AOA';

        public array $items = [];

        public object $payment;

        public function __construct(public string $status, string $method)
        {
            $this->payment = new class($method)
            {
                public array $additional = [];

                public function __construct(public string $method) {}

                public function update(array $data): void
                {
                    $this->additional = $data['additional'] ?? [];
                }
            };
        }

        public function canInvoice(): bool
        {
            return true;
        }
    };
}
