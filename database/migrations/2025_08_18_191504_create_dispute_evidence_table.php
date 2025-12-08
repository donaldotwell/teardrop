<?php

// Create dispute_evidence table migration
// php artisan make:migration create_dispute_evidence_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dispute_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('file_name');
            $table->longText('content'); // Base64 encoded file content
            $table->string('type'); // MIME type
            $table->string('file_type'); // image, document, etc.
            $table->bigInteger('file_size'); // in bytes
            $table->text('description')->nullable(); // Description of the evidence
            $table->enum('evidence_type', [
                'product_photo',      // Photo of received product
                'packaging_photo',    // Photo of packaging
                'shipping_label',     // Shipping documentation
                'receipt',           // Purchase receipt
                'communication',     // Screenshots of communication
                'damage_photo',      // Photo of damaged item
                'tracking_info',     // Shipping tracking information
                'other_document'     // Other supporting documents
            ]);
            $table->boolean('is_verified')->default(false); // Admin verified this evidence
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['dispute_id', 'evidence_type']);
            $table->index(['uploaded_by', 'created_at']);
            $table->index('is_verified');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dispute_evidence');
    }
};
