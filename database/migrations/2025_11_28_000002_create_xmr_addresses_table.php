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
        Schema::create('xmr_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xmr_wallet_id')->constrained()->onDelete('cascade');
            $table->string('address')->unique(); // Subaddress
            $table->integer('account_index')->default(0); // Account index
            $table->integer('address_index'); // Subaddress index
            $table->string('label')->nullable(); // Optional label for subaddress
            $table->decimal('balance', 20, 12)->default(0)->comment('DEPRECATED: Stale cached balance. Use XmrTransaction::where(xmr_address_id)->sum(amount) for accurate balance.');
            $table->decimal('total_received', 20, 12)->default(0);
            $table->integer('tx_count')->default(0); // Number of transactions
            $table->timestamp('first_used_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedBigInteger('last_synced_height')->default(0)->comment('Last blockchain height synced for this address - used to filter get_transfers() calls');
            $table->timestamp('last_synced_at')->nullable()->comment('Timestamp of last sync attempt for this address');
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->index(['xmr_wallet_id', 'address_index']);
            $table->index(['xmr_wallet_id', 'account_index', 'address_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xmr_addresses');
    }
};
