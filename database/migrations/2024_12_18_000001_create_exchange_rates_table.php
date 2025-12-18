<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('crypto_name'); // bitcoin, monero
            $table->string('crypto_shortname'); // btc, xmr
            $table->decimal('usd_rate', 20, 8); // Price in USD
            $table->timestamps();

            $table->unique('crypto_shortname');
            $table->index('crypto_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
