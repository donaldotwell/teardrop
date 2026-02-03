<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade'); // User who started dispute
            $table->foreignId('disputed_against')->constrained('users')->onDelete('cascade'); // Other party in dispute
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->onDelete('set null'); // Admin handling case
            $table->foreignId('assigned_moderator_id')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->nullable();
            $table->boolean('auto_assigned')->default(false);
            $table->timestamp('info_request_deadline')->nullable();

            $table->enum('type', [
                'item_not_received',
                'item_not_as_described',
                'damaged_item',
                'wrong_item',
                'quality_issue',
                'shipping_issue',
                'vendor_unresponsive',
                'refund_request',
                'other'
            ]);

            $table->enum('status', [
                'open',           // Newly created, waiting for admin
                'under_review',   // Admin is investigating
                'waiting_vendor', // Waiting for vendor response
                'waiting_buyer',  // Waiting for buyer response
                'escalated',      // Requires senior admin attention
                'resolved',       // Dispute resolved
                'closed'          // Dispute closed (no resolution needed)
            ])->default('open');

            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->string('subject', 255);
            $table->text('description');
            $table->decimal('disputed_amount', 10, 2); // Amount being disputed
            $table->text('buyer_evidence')->nullable(); // Buyer's evidence/explanation
            $table->text('vendor_response')->nullable(); // Vendor's response
            $table->text('admin_notes')->nullable(); // Internal admin notes
            $table->text('resolution_notes')->nullable(); // Final resolution explanation

            $table->enum('resolution', [
                'full_refund',           // Full refund to buyer
                'partial_refund',        // Partial refund to buyer
                'no_refund',            // No refund, vendor keeps payment
                'replacement',          // Vendor sends replacement
                'store_credit',         // Buyer gets store credit
                'custom_resolution'     // Custom arrangement
            ])->nullable();

            $table->decimal('refund_amount', 10, 2)->nullable(); // Amount refunded if applicable
            $table->timestamp('vendor_responded_at')->nullable();
            $table->timestamp('buyer_responded_at')->nullable();
            $table->timestamp('admin_reviewed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->text('escalation_reason')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['status', 'created_at']);
            $table->index(['assigned_admin_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('priority');
            $table->index('assigned_moderator_id');
            $table->index(['status', 'assigned_moderator_id']);
            $table->index(['auto_assigned', 'assigned_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('disputes');
    }
};
