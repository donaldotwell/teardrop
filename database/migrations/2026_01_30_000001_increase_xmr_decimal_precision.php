<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Increase XMR decimal precision from DECIMAL(16,12) to DECIMAL(20,12)
     * to support balances over 9,999 XMR.
     * 
     * Old: DECIMAL(16,12) = max 9999.999999999999 XMR
     * New: DECIMAL(20,12) = max 99999999.999999999999 XMR
     */
    public function up(): void
    {
        Schema::table('xmr_wallets', function (Blueprint $table) {
            $table->decimal('balance', 20, 12)->default(0)->change();
            $table->decimal('unlocked_balance', 20, 12)->default(0)->change();
            $table->decimal('total_received', 20, 12)->default(0)->change();
            $table->decimal('total_sent', 20, 12)->default(0)->change();
        });

        Schema::table('xmr_addresses', function (Blueprint $table) {
            $table->decimal('balance', 20, 12)->default(0)->change();
            $table->decimal('total_received', 20, 12)->default(0)->change();
        });

        Schema::table('xmr_transactions', function (Blueprint $table) {
            $table->decimal('amount', 20, 12)->change();
            $table->decimal('fee', 20, 12)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xmr_wallets', function (Blueprint $table) {
            $table->decimal('balance', 16, 12)->default(0)->change();
            $table->decimal('unlocked_balance', 16, 12)->default(0)->change();
            $table->decimal('total_received', 16, 12)->default(0)->change();
            $table->decimal('total_sent', 16, 12)->default(0)->change();
        });

        Schema::table('xmr_addresses', function (Blueprint $table) {
            $table->decimal('balance', 16, 12)->default(0)->change();
            $table->decimal('total_received', 16, 12)->default(0)->change();
        });

        Schema::table('xmr_transactions', function (Blueprint $table) {
            $table->decimal('amount', 16, 12)->change();
            $table->decimal('fee', 16, 12)->default(0)->change();
        });
    }
};
