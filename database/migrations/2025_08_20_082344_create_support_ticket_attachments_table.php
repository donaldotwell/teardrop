<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('support_ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');

            $table->string('file_name');
            $table->longText('content'); // Base64 encoded image content
            $table->string('type'); // Mime type
            $table->text('description')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('support_ticket_id');
            $table->index('uploaded_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('support_ticket_attachments');
    }
};
