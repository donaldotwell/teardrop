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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('total_early_finalized_orders')->default(0)->after('vendor_level');
            $table->integer('successful_early_finalized_orders')->default(0)->after('total_early_finalized_orders');
            $table->boolean('early_finalization_enabled')->default(true)->after('successful_early_finalized_orders');

            // Indexes
            $table->index('early_finalization_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['early_finalization_enabled']);
            $table->dropColumn([
                'total_early_finalized_orders',
                'successful_early_finalized_orders',
                'early_finalization_enabled'
            ]);
        });
    }
};
