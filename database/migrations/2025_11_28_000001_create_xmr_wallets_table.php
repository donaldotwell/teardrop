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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->unique(); // Wallet filename
            $table->string('primary_address')->unique(); // Main wallet address
            $table->text('view_key')->nullable(); // Private view key (encrypted)
            $table->text('spend_key_encrypted')->nullable(); // Encrypted private spend key
            $table->unsignedBigInteger('height')->default(0); // Blockchain sync height
            $table->decimal('balance', 16, 12)->default(0); // Current balance (12 decimals for XMR)
            $table->decimal('unlocked_balance', 16, 12)->default(0); // Available balance
            $table->decimal('total_received', 16, 12)->default(0); // Total XMR received
            $table->decimal('total_sent', 16, 12)->default(0); // Total XMR sent
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
