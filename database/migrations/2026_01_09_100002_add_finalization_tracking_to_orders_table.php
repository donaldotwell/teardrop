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
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_early_finalized')->default(false)->after('status');
            $table->timestamp('early_finalized_at')->nullable()->after('is_early_finalized');
            $table->timestamp('dispute_window_expires_at')->nullable()->after('early_finalized_at');
            $table->foreignId('finalization_window_id')->nullable()->after('dispute_window_expires_at');
            $table->string('direct_payment_txid')->nullable()->after('finalization_window_id');
            $table->string('admin_fee_txid')->nullable()->after('direct_payment_txid');

            // Indexes
            $table->index('is_early_finalized');
            $table->index('dispute_window_expires_at');
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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['finalization_window_id']);
            $table->dropIndex(['is_early_finalized']);
            $table->dropIndex(['dispute_window_expires_at']);
            $table->dropColumn([
                'is_early_finalized',
                'early_finalized_at',
                'dispute_window_expires_at',
                'finalization_window_id',
                'direct_payment_txid',
                'admin_fee_txid'
            ]);
        });
    }
};
