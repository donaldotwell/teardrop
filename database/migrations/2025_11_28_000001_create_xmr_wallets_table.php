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
            $table->string('name'); // Wallet filename
            $table->string('primary_address')->unique(); // Main wallet address
            $table->text('view_key')->nullable(); // Private view key (encrypted)
            $table->text('spend_key_encrypted')->nullable(); // Encrypted private spend key
            $table->text('seed_encrypted')->nullable(); // Encrypted 25-word mnemonic seed
            $table->string('password_hash')->nullable(); // Hashed wallet password for verification
            $table->unsignedBigInteger('height')->default(0); // Blockchain sync height
            $table->decimal('balance', 20, 12)->default(0)->comment('DEPRECATED: Stale RPC balance. Use XmrWallet::getBalance() for accurate balance from XmrTransaction sums.');
            $table->decimal('unlocked_balance', 20, 12)->default(0)->comment('DEPRECATED: Stale RPC balance. Use XmrWallet::getBalance() for accurate unlocked_balance from XmrTransaction sums.');
            $table->decimal('total_received', 20, 12)->default(0); // Total XMR received
            $table->decimal('total_sent', 20, 12)->default(0); // Total XMR sent
            $table->boolean('is_active')->default(true);
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
