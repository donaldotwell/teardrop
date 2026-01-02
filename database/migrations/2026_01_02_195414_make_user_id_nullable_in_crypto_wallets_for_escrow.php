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
        // Make user_id nullable in btc_wallets to support escrow wallets
        Schema::table('btc_wallets', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['user_id']);

            // Make user_id nullable
            $table->foreignId('user_id')->nullable()->change()->constrained()->onDelete('cascade');
        });

        // Make user_id nullable in xmr_wallets to support escrow wallets
        Schema::table('xmr_wallets', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['user_id']);

            // Make user_id nullable
            $table->foreignId('user_id')->nullable()->change()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore user_id as NOT NULL in btc_wallets
        Schema::table('btc_wallets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->change()->constrained()->onDelete('cascade');
        });

        // Restore user_id as NOT NULL in xmr_wallets
        Schema::table('xmr_wallets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->change()->constrained()->onDelete('cascade');
        });
    }
};
