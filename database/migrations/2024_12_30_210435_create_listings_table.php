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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->foreignId('user_id');
            $table->foreignId('product_id');
            $table->string('title', 140)->index();
            $table->string('short_description', 255)->index();
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->decimal('price_shipping', 10, 2);
            $table->enum('shipping_method', ['pickup', 'delivery', 'shipping'])->default('shipping');
            $table->enum('payment_method', ['escrow', 'direct'])->default('escrow');
            $table->date('end_date')->nullable()->index();
            $table->integer('quantity')->nullable()->index();
//            $table->integer('quantity_left')->nullable()->index();
            $table->foreignId('origin_country_id');
            $table->foreignId('destination_country_id');
            $table->json('tags')->nullable();
            $table->text('return_policy')->nullable();
            $table->integer('views')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
