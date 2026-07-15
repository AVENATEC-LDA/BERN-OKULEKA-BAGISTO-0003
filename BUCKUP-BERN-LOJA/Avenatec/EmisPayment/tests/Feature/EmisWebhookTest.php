<?php

use Avenatec\EmisPayment\Payment\EmisPayment;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderTransactionRepository;

use function Avenatec\EmisPayment\Tests\emisFakeOrder;

it('updates an accepted emis webhook to processing', function () {
    $order = emisFakeOrder();

    $this->mock(OrderRepository::class, function ($mock) use ($order) {
        $mock->shouldReceive('find')->with(1234)->andReturn($order);
        $mock->shouldReceive('update')->once()->with(['status' => 'processing'], 1234);
    });

    $this->mock(InvoiceRepository::class, function ($mock) {
        $mock->shouldReceive('create')->once()->andReturn((object) ['id' => 55]);
    });

    $this->mock(OrderTransactionRepository::class, function ($mock) {
        $mock->shouldReceive('create')->once();
    });

    $this->mock(CartRepository::class, function ($mock) {
        $mock->shouldReceive('find')->andReturn(null);
    });

    $this->app->instance(EmisPayment::class, new EmisPayment);

    $response = $this->postJson(route('emis_payment.webhook'), [
        'id'                      => 'TX123',
        'status'                  => 'ACCEPTED',
        'amount'                  => 300,
        'currency'                => 'AOA',
        'merchantReferenceNumber' => 'BERNO1234',
    ]);

    $response->assertSuccessful()->assertJson(['ok' => true]);
});

it('updates a rejected emis webhook to canceled', function () {
    $order = emisFakeOrder();

    $this->mock(OrderRepository::class, function ($mock) use ($order) {
        $mock->shouldReceive('find')->with(1234)->andReturn($order);
        $mock->shouldReceive('cancel')->once()->with($order, true)->andReturnTrue();
    });

    $this->mock(InvoiceRepository::class);
    $this->mock(OrderTransactionRepository::class);
    $this->mock(CartRepository::class);
    $this->app->instance(EmisPayment::class, new EmisPayment);

    $response = $this->postJson(route('emis_payment.webhook'), [
        'id'                      => 'TX123',
        'status'                  => 'REJECTED',
        'amount'                  => 300,
        'currency'                => 'AOA',
        'merchantReferenceNumber' => 'BERNO1234',
    ]);

    $response->assertSuccessful()->assertJson(['ok' => true]);
});

it('updates an accepted emis webhook from query string payload', function () {
    $order = emisFakeOrder();

    $this->mock(OrderRepository::class, function ($mock) use ($order) {
        $mock->shouldReceive('find')->with(1234)->andReturn($order);
        $mock->shouldReceive('update')->once()->with(['status' => 'processing'], 1234);
    });

    $this->mock(InvoiceRepository::class, function ($mock) {
        $mock->shouldReceive('create')->once()->andReturn((object) ['id' => 55]);
    });

    $this->mock(OrderTransactionRepository::class, function ($mock) {
        $mock->shouldReceive('create')->once();
    });

    $this->mock(CartRepository::class, function ($mock) {
        $mock->shouldReceive('find')->andReturn(null);
    });

    $this->app->instance(EmisPayment::class, new EmisPayment);

    $response = $this->getJson(route('emis_payment.webhook', [
        'transactionId'           => 'TX123',
        'transactionStatus'       => 'APPROVED',
        'merchantReferenceNumber' => 'BERNO1234',
    ]));

    $response->assertSuccessful()->assertJson(['ok' => true]);
});

it('confirms the emis webhook endpoint is publicly listening', function () {
    $response = $this->getJson(route('emis_payment.webhook'));

    $response->assertSuccessful()->assertJson([
        'ok'    => true,
        'ready' => true,
    ]);
});

it('does not update an already paid emis order', function () {
    $order = emisFakeOrder('processing');

    $this->mock(OrderRepository::class, function ($mock) use ($order) {
        $mock->shouldReceive('find')->with(1234)->andReturn($order);
        $mock->shouldReceive('update')->never();
    });

    $this->mock(InvoiceRepository::class);
    $this->mock(OrderTransactionRepository::class);
    $this->mock(CartRepository::class);
    $this->app->instance(EmisPayment::class, new EmisPayment);

    $response = $this->postJson(route('emis_payment.webhook'), [
        'id'                      => 'TX123',
        'status'                  => 'ACCEPTED',
        'merchantReferenceNumber' => 'BERNO1234',
    ]);

    $response->assertSuccessful()->assertJson(['ok' => true, 'note' => 'already_paid']);
});

it('rejects invalid emis webhook json', function () {
    $response = $this->post(route('emis_payment.webhook'), [], [
        'CONTENT_TYPE' => 'application/json',
    ]);

    $response->assertStatus(400);
});

it('returns not found for unknown emis references', function () {
    $this->mock(OrderRepository::class, function ($mock) {
        $mock->shouldReceive('find')->with(9999)->andThrow(new RuntimeException('missing'));
    });

    $this->mock(InvoiceRepository::class);
    $this->mock(OrderTransactionRepository::class);
    $this->mock(CartRepository::class);
    $this->app->instance(EmisPayment::class, new EmisPayment);

    $response = $this->postJson(route('emis_payment.webhook'), [
        'id'                      => 'TX999',
        'status'                  => 'ACCEPTED',
        'merchantReferenceNumber' => 'BERNO9999',
    ]);

    $response->assertNotFound();
});
