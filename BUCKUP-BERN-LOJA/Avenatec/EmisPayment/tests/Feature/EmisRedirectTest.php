<?php

use Avenatec\EmisPayment\Payment\EmisPayment;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderTransactionRepository;

use function Avenatec\EmisPayment\Tests\emisFakeOrder;

it('redirects emis payment without active session to cart', function () {
    $response = $this->get(route('emis_payment.redirect'));

    $response->assertRedirect(route('shop.checkout.cart.index'));
});

it('renders emis payment page with iframe when session has frame id', function () {
    $order = emisFakeOrder();
    $order->grand_total = 300.0;
    $order->order_currency_code = 'AOA';

    $this->mock(OrderRepository::class, function ($mock) use ($order) {
        $mock->shouldReceive('find')->with(1234)->andReturn($order);
    });

    $this->mock(InvoiceRepository::class);
    $this->mock(OrderTransactionRepository::class);
    $this->mock(CartRepository::class);

    $payment = new class extends EmisPayment
    {
        public function getConfigData($field)
        {
            return $field === 'frame_host'
                ? 'https://pagamentonline.emis.co.ao/online-payment-gateway/portal/frame?token='
                : null;
        }
    };

    $this->app->instance(EmisPayment::class, $payment);

    $response = $this
        ->withSession([
            'emis_frame_id' => 'frame-token-123',
            'emis_order_id' => 1234,
        ])
        ->get(route('emis_payment.pay'));

    $response->assertSuccessful()
        ->assertSee('https://pagamentonline.emis.co.ao/online-payment-gateway/portal/frame?token=frame-token-123', false)
        ->assertSee(route('emis_payment.status', 1234), false)
        ->assertSee('allowfullscreen="true"', false)
        ->assertSee("window.addEventListener('orientationchange', scaleFrame);", false)
        ->assertSee("var flowState = 'iframe';", false)
        ->assertSee('var processed = false;', false)
        ->assertDontSee('id="emis-status"', false)
        ->assertDontSee('id="emis-loader"', false)
        ->assertDontSee("scaleFrame();\n            pollOrderStatus();", false);
});

it('renders emis payment page from stored frame token when session is missing', function () {
    $order = emisFakeOrder();
    $order->grand_total = 300.0;
    $order->order_currency_code = 'AOA';
    $order->payment->additional = [
        'emis_frame_token' => \Illuminate\Support\Facades\Crypt::encryptString('frame-token-456'),
    ];

    $this->mock(OrderRepository::class, function ($mock) use ($order) {
        $mock->shouldReceive('find')->with(1234)->andReturn($order);
    });

    $this->mock(InvoiceRepository::class);
    $this->mock(OrderTransactionRepository::class);
    $this->mock(CartRepository::class);

    $payment = new class extends EmisPayment
    {
        public function getConfigData($field)
        {
            return $field === 'frame_host'
                ? 'https://pagamentonline.emis.co.ao/online-payment-gateway/portal/frame?token='
                : null;
        }
    };

    $this->app->instance(EmisPayment::class, $payment);

    $response = $this->get(route('emis_payment.pay', 1234));

    $response->assertSuccessful()
        ->assertSee('https://pagamentonline.emis.co.ao/online-payment-gateway/portal/frame?token=frame-token-456', false);
});

it('returns the emis order payment status', function () {
    $order = emisFakeOrder();

    $order->payment->additional = [
        'emis_status' => 'frame_token_created',
    ];

    $this->mock(OrderRepository::class, function ($mock) use ($order) {
        $mock->shouldReceive('find')->with(1234)->andReturn($order);
    });

    $this->mock(InvoiceRepository::class);
    $this->mock(OrderTransactionRepository::class);
    $this->mock(CartRepository::class);

    $this->app->instance(EmisPayment::class, new EmisPayment);

    $response = $this->getJson(route('emis_payment.status', 1234));

    $response->assertSuccessful()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('order_status', 'pending')
        ->assertJsonPath('payment_status', 'frame_token_created');
});
