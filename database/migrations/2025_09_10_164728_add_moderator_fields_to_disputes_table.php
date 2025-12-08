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
        Schema::table('disputes', function (Blueprint $table) {
            // Moderator assignment fields
            $table->foreignId('assigned_moderator_id')->nullable()->constrained('users')->after('assigned_admin_id');
            $table->timestamp('assigned_at')->nullable()->after('assigned_moderator_id');
            $table->boolean('auto_assigned')->default(false)->after('assigned_at');

            // Information request fields
            $table->timestamp('info_request_deadline')->nullable()->after('auto_assigned');

            // Escalation fields
            $table->text('escalation_reason')->nullable()->after('escalated_at');

            // Response tracking
            $table->timestamp('buyer_responded_at')->nullable()->after('vendor_responded_at');

            // Add indexes for performance
            $table->index('assigned_moderator_id');
            $table->index(['status', 'assigned_moderator_id']);
            $table->index(['auto_assigned', 'assigned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            //
        });
    }
};
