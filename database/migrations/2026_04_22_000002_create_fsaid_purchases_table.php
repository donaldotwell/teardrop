<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fsaid_purchases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('base_id')->nullable()->constrained('fsaid_bases')->onDelete('set null');
            $table->string('currency', 5);
            $table->decimal('total_usd', 12, 2);
            $table->decimal('total_crypto', 20, 12);
            $table->string('txid')->nullable();
            $table->unsignedInteger('record_count');
            $table->timestamps();

            $table->index('buyer_id');
            $table->index('vendor_id');
            $table->index('base_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fsaid_purchases');
    }
};
