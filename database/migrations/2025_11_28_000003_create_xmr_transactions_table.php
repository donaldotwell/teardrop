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
        Schema::create('xmr_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique()->comment('Unique identifier for external references');
            $table->foreignId('xmr_wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('xmr_address_id')->nullable()->constrained()->onDelete('set null');
            $table->string('txid')->nullable(); // Transaction hash
            $table->string('payment_id')->nullable(); // Optional payment ID
            $table->enum('type', ['deposit', 'withdrawal']);
            $table->decimal('amount', 20, 12); // 12 decimals for XMR
            $table->decimal('usd_value', 16, 2)->nullable();
            $table->decimal('fee', 20, 12)->default(0);
            $table->integer('confirmations')->default(0);
            $table->unsignedBigInteger('unlock_time')->default(0); // Monero-specific lock time
            $table->unsignedBigInteger('height')->nullable(); // Block height
            $table->enum('status', ['pending', 'confirmed', 'locked', 'unlocked', 'failed'])->default('pending');
            $table->json('raw_transaction')->nullable(); // Store raw transaction data
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->index(['xmr_wallet_id', 'type']);
            $table->index(['status', 'confirmations']);
            $table->index('txid'); // Index for lookup performance
            
            // Ensure same transaction can't be recorded twice for same wallet
            $table->unique(['txid', 'xmr_wallet_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xmr_transactions');
    }
};
