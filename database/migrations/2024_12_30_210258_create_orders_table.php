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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('user_id');
            $table->foreignId('listing_id');
            $table->string('currency');
            $table->integer('quantity');
            $table->decimal('usd_price', 10, 2);
            $table->decimal('crypto_value', 10, 8);
            $table->enum('status', ['pending', 'shipped', 'completed', 'cancelled'])->default('pending');
            $table->boolean('is_early_finalized')->default(false)->index();
            $table->timestamp('early_finalized_at')->nullable();
            $table->timestamp('dispute_window_expires_at')->nullable()->index();
            $table->unsignedBigInteger('finalization_window_id')->nullable();
            $table->string('direct_payment_txid')->nullable();
            $table->string('admin_fee_txid')->nullable();
            $table->decimal('admin_fee_crypto', 20, 12)->nullable(); // Admin fee owed in crypto (collected on vendor withdrawal)
            $table->string('admin_fee_currency', 3)->nullable(); // Currency of the admin fee (btc/xmr)
            $table->string('txid')->nullable(); // Blockchain transaction ID for completed orders
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('encrypted_delivery_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
