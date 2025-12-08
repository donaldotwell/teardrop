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
        Schema::create('btc_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->unique();
            $table->string('xpub')->nullable(); // Extended public key for HD wallet
            $table->string('address_index')->default(0); // Current address index
            $table->decimal('total_received', 16, 8)->default(0); // Total BTC received
            $table->decimal('total_sent', 16, 8)->default(0); // Total BTC sent
            $table->decimal('balance', 16, 8)->default(0); // Current balance
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('btc_wallets');
    }
};
