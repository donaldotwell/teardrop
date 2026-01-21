<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Modify the enum type
            DB::statement("ALTER TABLE dispute_messages ALTER COLUMN message_type TYPE VARCHAR(50)");
            DB::statement("DROP TYPE IF EXISTS dispute_messages_message_type CASCADE");

            DB::statement("
                CREATE TYPE dispute_messages_message_type AS ENUM (
                    'user_message',
                    'admin_message',
                    'system_message',
                    'status_update',
                    'evidence_upload',
                    'resolution_note',
                    'assignment_update'
                )
            ");

            DB::statement("ALTER TABLE dispute_messages ALTER COLUMN message_type TYPE dispute_messages_message_type USING message_type::dispute_messages_message_type");
            DB::statement("ALTER TABLE dispute_messages ALTER COLUMN message_type SET DEFAULT 'user_message'");
        } else {
            // MySQL: Recreate the column with new enum values
            Schema::table('dispute_messages', function (Blueprint $table) {
                $table->enum('message_type', [
                    'user_message',
                    'admin_message',
                    'system_message',
                    'status_update',
                    'evidence_upload',
                    'resolution_note',
                    'assignment_update'
                ])->default('user_message')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Remove 'assignment_update' from enum
            DB::statement("ALTER TABLE dispute_messages ALTER COLUMN message_type TYPE VARCHAR(50)");
            DB::statement("DROP TYPE IF EXISTS dispute_messages_message_type CASCADE");

            DB::statement("
                CREATE TYPE dispute_messages_message_type AS ENUM (
                    'user_message',
                    'admin_message',
                    'system_message',
                    'status_update',
                    'evidence_upload',
                    'resolution_note'
                )
            ");

            DB::statement("ALTER TABLE dispute_messages ALTER COLUMN message_type TYPE dispute_messages_message_type USING message_type::dispute_messages_message_type");
            DB::statement("ALTER TABLE dispute_messages ALTER COLUMN message_type SET DEFAULT 'user_message'");
        } else {
            // MySQL: Recreate the column without assignment_update
            Schema::table('dispute_messages', function (Blueprint $table) {
                $table->enum('message_type', [
                    'user_message',
                    'admin_message',
                    'system_message',
                    'status_update',
                    'evidence_upload',
                    'resolution_note'
                ])->default('user_message')->change();
            });
        }
    }
};
