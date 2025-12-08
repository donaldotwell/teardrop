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
            $table->text('seed_encrypted')->nullable()->after('spend_key_encrypted'); // Encrypted 25-word mnemonic seed
            $table->string('password_hash')->nullable()->after('seed_encrypted'); // Hashed wallet password for verification
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xmr_wallets', function (Blueprint $table) {
            $table->dropColumn(['seed_encrypted', 'password_hash']);
        });
    }
};
