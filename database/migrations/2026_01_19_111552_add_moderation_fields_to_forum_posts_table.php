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
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('body');
            $table->foreignId('assigned_moderator_id')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete()->after('assigned_moderator_id');
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');
            $table->text('moderation_notes')->nullable()->after('moderated_at');

            $table->index('status');
            $table->index('assigned_moderator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->dropForeign(['assigned_moderator_id']);
            $table->dropForeign(['moderated_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['assigned_moderator_id']);
            $table->dropColumn(['status', 'assigned_moderator_id', 'moderated_by', 'moderated_at', 'moderation_notes']);
        });
    }
};
