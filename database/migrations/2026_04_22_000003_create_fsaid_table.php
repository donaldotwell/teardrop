<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fsaid', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('base_id')->constrained('fsaid_bases')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');

            // Identity
            $table->string('first_name');
            $table->string('last_name');
            $table->string('dob', 30)->nullable();
            $table->string('ssn', 20)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('cs')->nullable();          // credit score
            $table->text('description')->nullable();

            // Account credentials
            $table->string('email');
            $table->string('email_pass');
            $table->string('fa_uname')->nullable();
            $table->string('fa_pass')->nullable();
            $table->text('backup_code')->nullable();
            $table->text('security_qa')->nullable();
            $table->string('two_fa')->nullable();

            // Financial aid / enrollment data
            $table->string('level')->nullable();
            $table->text('programs')->nullable();
            $table->string('enrollment')->nullable();
            $table->text('enrollment_details')->nullable();

            // Platform sale tracking
            $table->enum('status', ['available', 'sold'])->default('available');
            $table->foreignId('platform_buyer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('platform_purchase_id')->nullable()->constrained('fsaid_purchases')->onDelete('set null');
            $table->timestamp('sold_at')->nullable();

            $table->timestamps();

            $table->index(['base_id', 'status']);
            $table->index('vendor_id');
            $table->index('platform_buyer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fsaid');
    }
};
