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
        Schema::create('xmr_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name')->unique(); // Unique wallet filename (e.g. 'user_42', 'escrow_order_12_xmr')
            $table->string('primary_address')->unique(); // Main wallet address (account 0, index 0)
            $table->text('view_key')->nullable(); // Private view key (encrypted)
            $table->text('spend_key_encrypted')->nullable(); // Encrypted private spend key
            $table->text('seed_encrypted')->nullable(); // Encrypted 25-word mnemonic seed
            $table->text('password_encrypted')->nullable(); // Wallet password encrypted with APP_KEY (decryptable for open_wallet RPC)
            $table->unsignedBigInteger('height')->default(0); // Blockchain sync height
            $table->decimal('balance', 20, 12)->default(0); // Total balance from RPC get_balance (authoritative, updated by sync job)
            $table->decimal('unlocked_balance', 20, 12)->default(0); // Unlocked/spendable balance from RPC (authoritative, updated by sync job)
            $table->decimal('total_received', 20, 12)->default(0); // Total XMR received
            $table->decimal('total_sent', 20, 12)->default(0); // Total XMR sent
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable(); // Last successful sync timestamp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xmr_wallets');
    }
};
