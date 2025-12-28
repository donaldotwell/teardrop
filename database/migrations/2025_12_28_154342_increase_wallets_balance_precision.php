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
        Schema::table('wallets', function (Blueprint $table) {
            // Change balance precision from DECIMAL(10, 8) to DECIMAL(20, 8)
            // This allows values up to 999,999,999,999.99999999
            // Old: max 99.99999999 (too small for realistic crypto amounts)
            // New: max 999,999,999,999.99999999 (sufficient for crypto wallets)
            $table->decimal('balance', 20, 8)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Revert back to original precision (not recommended in production)
            $table->decimal('balance', 10, 8)->default(0)->change();
        });
    }
};
