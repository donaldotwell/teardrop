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
        Schema::table('product_categories', function (Blueprint $table) {
            $table->boolean('allows_early_finalization')->default(false)->after('name');
            $table->foreignId('finalization_window_id')->nullable()->after('allows_early_finalization');
            $table->integer('min_vendor_level_for_early')->default(8)->after('finalization_window_id');
            $table->text('early_finalization_notes')->nullable()->after('min_vendor_level_for_early');

            // Indexes
            $table->index('allows_early_finalization');
            $table->foreign('finalization_window_id')
                  ->references('id')
                  ->on('finalization_windows')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropForeign(['finalization_window_id']);
            $table->dropIndex(['allows_early_finalization']);
            $table->dropColumn([
                'allows_early_finalization',
                'finalization_window_id',
                'min_vendor_level_for_early',
                'early_finalization_notes'
            ]);
        });
    }
};
