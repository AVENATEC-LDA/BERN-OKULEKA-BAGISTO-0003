<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id')->unique();
            $table->uuid('fleet_order_id')->nullable()->index();
            $table->string('external_reference')->index();
            $table->uuid('idempotency_key');
            $table->string('tracking_code')->nullable();
            $table->string('tracking_url')->nullable();
            // pending_dispatch | dispatched | dispatch_failed | assigned |
            // picked_up | in_transit | delivered | failed | cancelled
            $table->string('status')->default('pending_dispatch');
            $table->unsignedBigInteger('fee_aoa')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('last_payload')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_orders');
    }
};
