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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained(); // Who performed the action
            $table->foreignId('target_user_id')->nullable()->constrained('users'); // Who was affected
            $table->string('action'); // e.g., 'user_banned', 'post_reported', 'report_reviewed'
            $table->json('details')->nullable(); // Additional context
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at');

            $table->index(['action', 'created_at']);
            $table->index('target_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
