<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fullz_bases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->string('name');                               // Vendor-given name for this upload
            $table->decimal('price_usd', 10, 2);                 // Price per record in USD
            $table->unsignedInteger('record_count')->default(0);
            $table->unsignedInteger('available_count')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('vendor_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fullz_bases');
    }
};
