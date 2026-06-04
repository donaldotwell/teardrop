<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_id')->constrained('card_bases')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');

            // Card data
            $table->string('card_number', 25);
            $table->string('exp_month', 2);
            $table->string('exp_year', 4);
            $table->string('cvv', 6);
            $table->string('cardholder_name');

            // Address / contact
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('country', 60)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('phone', 30)->nullable();

            // Pricing & sale tracking
            $table->decimal('price_usd', 10, 2);
            $table->enum('status', ['available', 'sold'])->default('available');
            $table->foreignId('buyer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('purchase_id')->nullable()->constrained('card_purchases')->onDelete('set null');
            $table->timestamp('sold_at')->nullable();

            $table->timestamps();

            $table->index(['base_id', 'status']);
            $table->index('vendor_id');
            $table->index('buyer_id');
            $table->index('purchase_id');
            $table->index('state');
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
