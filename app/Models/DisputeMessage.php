<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisputeMessage extends Model
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
            'is_internal' => 'boolean',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Get the dispute that owns this message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dispute(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    /**
     * Get the user who sent this message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark this message as read.
     *
     * @return void
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Check if this message is visible to a specific user.
     *
     * @param User $user
     * @return bool
     */
    public function isVisibleTo(User $user): bool
    {
        // Internal messages are only visible to admins
        if ($this->is_internal) {
            return $user->hasRole('admin');
        }

        // Public messages are visible to dispute participants and admins
        return $this->dispute->canUserParticipate($user);
    }

    /**
     * Get the message type color for display.
     *
     * @return string
     */
    public function getTypeColor(): string
    {
        return match($this->message_type) {
            'user_message' => 'blue',
            'admin_message' => 'purple',
            'system_message' => 'gray',
            'status_update' => 'yellow',
            'evidence_upload' => 'green',
            'resolution_note' => 'emerald',
            default => 'gray'
        };
    }

    /**
     * Check if this is a system-generated message.
     *
     * @return bool
     */
    public function isSystemMessage(): bool
    {
        return in_array($this->message_type, ['system_message', 'status_update']);
    }

    /**
     * Scope for public messages only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope for internal messages only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Scope for unread messages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
