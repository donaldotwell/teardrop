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
        Schema::create('btc_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('btc_wallet_id')->constrained()->onDelete('cascade');
            $table->string('address')->unique();
            $table->integer('address_index'); // HD wallet derivation index
            $table->decimal('balance', 16, 8)->default(0);
            $table->decimal('total_received', 16, 8)->default(0);
            $table->integer('tx_count')->default(0); // Number of transactions
            $table->timestamp('first_used_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->index(['btc_wallet_id', 'address_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('btc_addresses');
    }
};
