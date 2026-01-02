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
        Schema::create('escrow_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->onDelete('cascade');
            $table->enum('currency', ['btc', 'xmr']);
            $table->string('wallet_name')->unique();
            $table->string('wallet_password_hash')->nullable(); // For XMR
            $table->string('address'); // Primary receiving address
            $table->decimal('balance', 20, 12)->default(0);
            $table->enum('status', ['active', 'released', 'refunded', 'archived'])->default('active');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['currency', 'status']);
        });

        // Add escrow wallet reference to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('escrow_wallet_id')->nullable()->after('txid')->constrained();
            $table->timestamp('escrow_funded_at')->nullable()->after('escrow_wallet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['escrow_wallet_id']);
            $table->dropColumn(['escrow_wallet_id', 'escrow_funded_at']);
        });

        Schema::dropIfExists('escrow_wallets');
    }
};
