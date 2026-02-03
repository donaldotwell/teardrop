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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('allows_early_finalization')->default(false);
            $table->unsignedBigInteger('finalization_window_id')->nullable();
            $table->integer('min_vendor_level_for_early')->default(8);
            $table->text('early_finalization_notes')->nullable();
            $table->timestamps();

            $table->index('allows_early_finalization');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
