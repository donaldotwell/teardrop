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
        Schema::create('finalization_windows', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->integer('duration_minutes')->comment('Duration in minutes for dispute window');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finalization_windows');
    }
};
