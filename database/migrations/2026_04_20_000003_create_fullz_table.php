<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fullz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_id')->constrained('fullz_bases')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');

            // PII fields from CSV
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('phone_no', 30)->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('ssn', 20);
            $table->string('dob', 20);

            // Sale tracking
            $table->enum('status', ['available', 'sold'])->default('available');
            $table->foreignId('buyer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('purchase_id')->nullable()->constrained('fullz_purchases')->onDelete('set null');
            $table->timestamp('sold_at')->nullable();

            $table->timestamps();

            $table->index(['base_id', 'status']);
            $table->index('vendor_id');
            $table->index('buyer_id');
            $table->index('purchase_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fullz');
    }
};
