<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('ticket_number', 20)->unique(); // Format: ST-YYYYMMDD-####

            // User and assignment
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            // Ticket details
            $table->string('subject');
            $table->text('description');
            $table->enum('type', [
                'account_banned',
                'account_suspended',
                'account_verification',
                'login_issues',
                'password_reset',
                'btc_deposit_issue',
                'btc_withdrawal_issue',
                'xmr_deposit_issue',
                'xmr_withdrawal_issue',
                'balance_discrepancy',
                'escrow_issue',
                'order_problem',
                'listing_issue',
                'vendor_application',
                'vendor_verification',
                'trust_level_inquiry',
                'fee_inquiry',
                'refund_request',
                'technical_issue',
                'bug_report',
                'feature_request',
                'general_inquiry',
                'other'
            ]);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'pending', 'in_progress', 'on_hold', 'resolved', 'closed'])->default('open');

            // Categories for better organization
            $table->enum('category', [
                'account',
                'payments',
                'orders',
                'technical',
                'general'
            ]);

            // Metadata
            $table->json('metadata')->nullable(); // For storing additional context like crypto addresses, order IDs, etc.
            $table->text('resolution_notes')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['type', 'status']);
            $table->index(['priority', 'status']);
            $table->index('ticket_number');
            $table->index('last_activity_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('support_tickets');
    }
};
