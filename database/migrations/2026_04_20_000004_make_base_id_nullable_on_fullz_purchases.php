<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fullz_purchases', function (Blueprint $table) {
            // Allow null when a purchase spans records from multiple bases (same vendor)
            $table->foreignId('base_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fullz_purchases', function (Blueprint $table) {
            $table->foreignId('base_id')->nullable(false)->change();
        });
    }
};
