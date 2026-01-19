<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'status',
        'assigned_moderator_id',
        'moderated_by',
        'moderated_at',
        'moderation_notes',
        'is_locked',
        'is_pinned',
        'views_count',
        'last_activity_at',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_pinned' => 'boolean',
        'last_activity_at' => 'datetime',
        'moderated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedModerator()
    {
        return $this->belongsTo(User::class, 'assigned_moderator_id');
    }

    public function moderatedBy()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function comments()
    {
        return $this->hasMany(ForumComment::class)->whereNull('parent_id')->latest();
    }

    public function allComments()
    {
        return $this->hasMany(ForumComment::class);
    }

    public function reports()
    {
        return $this->morphMany(ForumReport::class, 'reportable');
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function updateLastActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('body', 'LIKE', "%{$search}%");
        });
    }

    public function scopeByAuthor($query, $username)
    {
        return $query->whereHas('user', function($q) use ($username) {
            $q->where('username_pub', $username);
        });
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
