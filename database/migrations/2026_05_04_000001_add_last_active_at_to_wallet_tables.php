<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('btc_wallets', function (Blueprint $table) {
            $table->timestamp('last_active_at')->nullable()->after('is_active');
        });

        Schema::table('xmr_wallets', function (Blueprint $table) {
            $table->timestamp('last_active_at')->nullable()->after('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('btc_wallets', function (Blueprint $table) {
            $table->dropColumn('last_active_at');
        });

        Schema::table('xmr_wallets', function (Blueprint $table) {
            $table->dropColumn('last_active_at');
        });
    }
};
