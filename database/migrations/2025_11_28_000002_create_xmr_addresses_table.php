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
        Schema::create('xmr_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xmr_wallet_id')->constrained()->onDelete('cascade');
            $table->string('address')->unique(); // Subaddress
            $table->integer('account_index')->default(0); // Account index
            $table->integer('address_index'); // Subaddress index
            $table->string('label')->nullable(); // Optional label for subaddress
            $table->decimal('balance', 16, 12)->default(0);
            $table->decimal('total_received', 16, 12)->default(0);
            $table->integer('tx_count')->default(0); // Number of transactions
            $table->timestamp('first_used_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->index(['xmr_wallet_id', 'address_index']);
            $table->index(['xmr_wallet_id', 'account_index', 'address_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xmr_addresses');
    }
};
