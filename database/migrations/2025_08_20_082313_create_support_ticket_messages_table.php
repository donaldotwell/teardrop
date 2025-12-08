<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->text('message');
            $table->enum('message_type', [
                'user_message',
                'staff_message',
                'system_message',
                'status_update',
                'assignment_update',
                'priority_update',
                'note'
            ])->default('user_message');

            $table->boolean('is_internal')->default(false); // Internal notes between staff
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['support_ticket_id', 'created_at']);
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('support_ticket_messages');
    }
};
