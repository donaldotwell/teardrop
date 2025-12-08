<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Dispute extends Model
{
    use HasFactory;

    /**
     * The attributes that are guarded.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vendor_responded_at' => 'datetime',
            'admin_reviewed_at' => 'datetime',
            'assigned_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'escalated_at' => 'datetime',
            'disputed_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    /**
     * Get the order that is being disputed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who initiated the dispute.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function initiatedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get the user who is being disputed against.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function disputedAgainst(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'disputed_against');
    }

    /**
     * Get the admin assigned to this dispute.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedAdmin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    /**
     * Get the messages for this dispute.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DisputeMessage::class);
    }

    /**
     * Get the public messages for this dispute (non-internal).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publicMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DisputeMessage::class)->where('is_internal', false);
    }

    /**
     * Get the internal messages for this dispute (admin only).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function internalMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DisputeMessage::class)->where('is_internal', true);
    }

    /**
     * Get the evidence for this dispute.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function evidence(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DisputeEvidence::class);
    }

    /**
     * Get the verified evidence for this dispute.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function verifiedEvidence(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DisputeEvidence::class)->where('is_verified', true);
    }

    /**
     * Check if the dispute is open (not resolved or closed).
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return !in_array($this->status, ['resolved', 'closed']);
    }

    /**
     * Check if the dispute is resolved.
     *
     * @return bool
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if the dispute is closed.
     *
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Check if the dispute can be escalated.
     *
     * @return bool
     */
    public function canBeEscalated(): bool
    {
        return $this->isOpen() && $this->status !== 'escalated';
    }

    /**
     * Check if a user can participate in this dispute.
     *
     * @param User $user
     * @return bool
     */
    public function canUserParticipate(User $user): bool
    {
        return $user->id === $this->initiated_by ||
            $user->id === $this->disputed_against ||
            $user->hasRole('admin');
    }

    /**
     * Get the other party in the dispute (not the current user).
     *
     * @param User $currentUser
     * @return User|null
     */
    public function getOtherParty(User $currentUser): ?User
    {
        if ($currentUser->id === $this->initiated_by) {
            return $this->disputedAgainst;
        } elseif ($currentUser->id === $this->disputed_against) {
            return $this->initiatedBy;
        }

        return null;
    }

    /**
     * Mark the dispute as resolved.
     *
     * @param string $resolution
     * @param float|null $refundAmount
     * @param string|null $notes
     * @return void
     */
    public function markAsResolved(string $resolution, ?float $refundAmount = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution' => $resolution,
            'refund_amount' => $refundAmount,
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Escalate the dispute to higher priority.
     *
     * @param string|null $reason
     * @return void
     */
    public function escalate(?string $reason = null): void
    {
        $this->update([
            'status' => 'escalated',
            'priority' => 'high',
            'escalated_at' => now(),
        ]);

        // Add system message about escalation
        $this->messages()->create([
            'user_id' => $this->assigned_admin_id ?? auth()->id(),
            'message' => "Dispute escalated. Reason: " . ($reason ?? 'No reason provided'),
            'message_type' => 'status_update',
            'is_internal' => true,
        ]);
    }

    /**
     * Assign an admin to this dispute.
     *
     * @param User $admin
     * @return void
     */
    public function assignAdmin(User $admin): void
    {
        $this->update([
            'assigned_admin_id' => $admin->id,
            'status' => 'under_review',
            'admin_reviewed_at' => now(),
        ]);

        // Add system message about assignment
        $this->messages()->create([
            'user_id' => $admin->id,
            'message' => "Dispute assigned to admin: {$admin->username_pub}",
            'message_type' => 'status_update',
            'is_internal' => true,
        ]);
    }

    /**
     * Get the status color for display.
     *
     * @return string
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'open' => 'yellow',
            'under_review' => 'blue',
            'waiting_vendor' => 'orange',
            'waiting_buyer' => 'purple',
            'escalated' => 'red',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get the priority color for display.
     *
     * @return string
     */
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

    /**
     * Scope for open disputes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed']);
    }

    /**
     * Scope for disputes assigned to a specific admin.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param User $admin
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssignedTo($query, User $admin)
    {
        return $query->where('assigned_admin_id', $admin->id);
    }

    /**
     * Scope for high priority disputes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    /**
     * Get the moderator assigned to this dispute
     */
    public function assignedModerator()
    {
        return $this->belongsTo(User::class, 'assigned_moderator_id');
    }
}
