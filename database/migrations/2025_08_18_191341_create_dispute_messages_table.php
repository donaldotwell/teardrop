<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dispute_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who sent the message
            $table->text('message');
            $table->enum('message_type', [
                'user_message',     // Regular message from buyer/vendor
                'admin_message',    // Message from admin
                'system_message',   // Automated system message
                'status_update',    // Status change notification
                'evidence_upload',  // Evidence submission
                'resolution_note',  // Final resolution message
                'assignment_update' // Admin assignment/reassignment notification
            ])->default('user_message');

            $table->boolean('is_internal')->default(false); // Admin-only messages
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['dispute_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('is_internal');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dispute_messages');
    }
};
