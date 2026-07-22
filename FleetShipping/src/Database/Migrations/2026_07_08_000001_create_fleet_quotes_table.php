<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_quotes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('cart_id')->index();
            $table->uuid('quote_id');
            $table->unsignedBigInteger('fee_aoa');
            $table->unsignedInteger('eta_minutes')->nullable();
            $table->float('distance_km')->nullable();
            $table->timestamp('valid_until');
            $table->json('origin');
            $table->json('destination');
            $table->json('parcel');
            $table->boolean('redeemed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_quotes');
    }
};
