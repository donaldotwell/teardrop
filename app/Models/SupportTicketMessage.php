<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicketMessage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function isVisibleTo(User $user): bool
    {
        // Internal messages only visible to staff/admin
        if ($this->is_internal) {
            return $user->hasAnyRole(['admin', 'support']);
        }

        // Public messages visible to ticket participants and staff
        return $this->supportTicket->canUserParticipate($user);
    }

    public function getTypeColor(): string
    {
        return match($this->message_type) {
            'user_message' => 'blue',
            'staff_message' => 'purple',
            'system_message' => 'gray',
            'status_update' => 'yellow',
            'assignment_update' => 'green',
            'priority_update' => 'orange',
            'note' => 'indigo',
            default => 'gray'
        };
    }

    protected static function boot(): void
    {
        parent::boot();

        static::created(function ($model) {
            // Update ticket's first response time if this is first staff response
            $ticket = $model->supportTicket;

            if (!$ticket->first_response_at &&
                $model->user->hasAnyRole(['admin', 'support']) &&
                $model->message_type === 'staff_message') {
                $ticket->update(['first_response_at' => $model->created_at]);
            }
        });
    }
}
