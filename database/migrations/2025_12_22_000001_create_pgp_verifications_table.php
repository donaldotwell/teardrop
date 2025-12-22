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
        Schema::create('pgp_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('pgp_pub_key'); // The PGP key being verified
            $table->string('verification_code', 64); // Random code to be decrypted
            $table->text('encrypted_message'); // The encrypted challenge message
            $table->enum('status', ['pending', 'verified', 'failed', 'expired'])->default('pending');
            $table->timestamp('expires_at'); // Verification expires after 1 hour
            $table->timestamp('verified_at')->nullable();
            $table->integer('attempts')->default(0); // Track failed attempts
            $table->timestamps();

            // Index for cleanup of expired verifications
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pgp_verifications');
    }
};
