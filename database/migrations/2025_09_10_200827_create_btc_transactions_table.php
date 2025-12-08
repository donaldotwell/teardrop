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
        Schema::create('btc_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('btc_wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('btc_address_id')->nullable()->constrained()->onDelete('set null');
            $table->string('txid')->nullable(); // Not unique - same txid can exist for sender and receiver
            $table->enum('type', ['deposit', 'withdrawal']);
            $table->decimal('amount', 16, 8);
            $table->decimal('fee', 16, 8)->default(0);
            $table->integer('confirmations')->default(0);
            $table->enum('status', ['pending', 'confirmed', 'failed'])->default('pending');
            $table->json('raw_transaction')->nullable(); // Store raw transaction data
            $table->string('block_hash')->nullable();
            $table->integer('block_height')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['btc_wallet_id', 'type']);
            $table->index(['status', 'confirmations']);
            $table->index('txid'); // Index for lookup performance
            
            // Ensure same transaction can't be recorded twice for same wallet
            // This allows same txid for different wallets (sender/receiver)
            $table->unique(['txid', 'btc_wallet_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('btc_transactions');
    }
};
