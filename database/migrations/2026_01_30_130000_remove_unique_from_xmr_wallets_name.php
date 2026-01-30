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
        Schema::table('xmr_wallets', function (Blueprint $table) {
            // Drop unique constraint from name column
            // All users now share the same master wallet name
            $table->dropUnique(['name']);
            
            // Make sure primary_address is still unique
            // Each user gets their own unique subaddress
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xmr_wallets', function (Blueprint $table) {
            // Restore unique constraint on name column
            $table->unique('name');
        });
    }
};
