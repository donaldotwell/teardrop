<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            $model->ticket_number = self::generateTicketNumber();
            $model->last_activity_at = now();
        });

        static::updating(function ($model) {
            $model->last_activity_at = now();
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(SupportTicketMessage::class);
    }

    public function publicMessages()
    {
        return $this->hasMany(SupportTicketMessage::class)->where('is_internal', false);
    }

    public function internalMessages()
    {
        return $this->hasMany(SupportTicketMessage::class)->where('is_internal', true);
    }

    public function attachments()
    {
        return $this->hasMany(SupportTicketAttachment::class);
    }

    // Utility methods
    public static function generateTicketNumber(): string
    {
        $date = now()->format('Ymd');
        $latest = self::whereDate('created_at', today())
            ->latest('id')
            ->first();

        $sequence = $latest ? (int) substr($latest->ticket_number, -4) + 1 : 1;

        return 'ST-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function getTicketTypes(): array
    {
        return [
            'account' => [
                'account_banned' => 'Account Banned Appeal',
                'account_suspended' => 'Account Suspended Issue',
                'account_verification' => 'Account Verification Help',
                'login_issues' => 'Login Problems',
                'password_reset' => 'Password Reset Help',
                'vendor_application' => 'Vendor Application Status',
                'vendor_verification' => 'Vendor Verification Help',
                'trust_level_inquiry' => 'Trust Level Questions',
            ],
            'payments' => [
                'btc_deposit_issue' => 'Bitcoin Deposit Problem',
                'btc_withdrawal_issue' => 'Bitcoin Withdrawal Problem',
                'xmr_deposit_issue' => 'Monero Deposit Problem',
                'xmr_withdrawal_issue' => 'Monero Withdrawal Problem',
                'balance_discrepancy' => 'Balance Discrepancy',
                'escrow_issue' => 'Escrow Service Issue',
                'fee_inquiry' => 'Fee Questions',
                'refund_request' => 'Refund Request',
            ],
            'orders' => [
                'order_problem' => 'Order Issue',
                'listing_issue' => 'Listing Problem',
            ],
            'technical' => [
                'technical_issue' => 'Technical Problem',
                'bug_report' => 'Bug Report',
                'feature_request' => 'Feature Request',
            ],
            'general' => [
                'general_inquiry' => 'General Question',
                'other' => 'Other Issue',
            ]
        ];
    }

    public function getTypeDisplayName(): string
    {
        $types = collect(self::getTicketTypes())->flatten();
        return $types[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getCategoryDisplayName(): string
    {
        return ucfirst($this->category);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'pending', 'in_progress']);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function canUserParticipate(User $user): bool
    {
        return $user->id === $this->user_id ||
            $user->id === $this->assigned_to ||
            $user->hasAnyRole(['admin', 'support']);
    }

    public function markAsResolved(string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);
    }

    public function markAsClosed(string $notes = null): void
    {
        $this->update([
            'status' => 'closed',
            'resolution_notes' => $notes,
            'closed_at' => now(),
        ]);
    }

    public function assignTo(User $staff): void
    {
        $this->update([
            'assigned_to' => $staff->id,
            'status' => $this->status === 'open' ? 'in_progress' : $this->status,
        ]);

        // Add system message
        $this->messages()->create([
            'user_id' => $staff->id,
            'message' => "Ticket assigned to {$staff->username_pub}",
            'message_type' => 'assignment_update',
            'is_internal' => true,
        ]);
    }

    public function updatePriority(string $priority, User $updatedBy): void
    {
        $oldPriority = $this->priority;
        $this->update(['priority' => $priority]);

        // Add system message
        $this->messages()->create([
            'user_id' => $updatedBy->id,
            'message' => "Priority changed from {$oldPriority} to {$priority}",
            'message_type' => 'priority_update',
            'is_internal' => true,
        ]);
    }

    public function updateStatus(string $status, User $updatedBy): void
    {
        $oldStatus = $this->status;
        $this->update(['status' => $status]);

        // Add system message
        $this->messages()->create([
            'user_id' => $updatedBy->id,
            'message' => "Status changed from {$oldStatus} to {$status}",
            'message_type' => 'status_update',
            'is_internal' => false,
        ]);
    }

    public function hasUnreadMessagesFor(User $user): bool
    {
        return $this->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_internal', false)
            ->where('is_read', false)
            ->exists();
    }

    public function getUnreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_internal', false)
            ->where('is_read', false)
            ->count();
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'open' => 'yellow',
            'pending' => 'orange',
            'in_progress' => 'blue',
            'on_hold' => 'purple',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray'
        };
    }

    public function getPriorityColor(): string
    {
        return match($this->priority) {
            'low' => 'gray',
            'medium' => 'blue',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray'
        };
    }

    public function getResponseTime(): ?int
    {
        if (!$this->first_response_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->first_response_at);
    }

    public function getResolutionTime(): ?int
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffInHours($this->resolved_at);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'pending', 'in_progress']);
    }

    public function scopeAssignedTo($query, User $user)
    {
        return $query->where('assigned_to', $user->id);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    public function scopeRecentActivity($query, int $hours = 24)
    {
        return $query->where('last_activity_at', '>=', now()->subHours($hours));
    }

    public function scopeOverdue($query, int $hours = 48)
    {
        return $query->open()
            ->where('created_at', '<=', now()->subHours($hours))
            ->whereNull('first_response_at');
    }
}
