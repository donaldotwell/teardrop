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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username_pri', 30)->index();
            $table->string('username_pub', 30)->index();
            $table->string('pin', 128);
            $table->string('password', 128);
            $table->string('passphrase_1', 140);
            $table->string('passphrase_2', 140)->nullable();
            $table->integer('trust_level')->default(1);
            $table->integer('vendor_level')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_early_finalized_orders')->default(0);
            $table->integer('successful_early_finalized_orders')->default(0);
            $table->boolean('early_finalization_enabled')->default(true)->index();
            $table->timestamp('vendor_since')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            $table->text('pgp_pub_key')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });



        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
