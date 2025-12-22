<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PgpVerification extends Model
{
    protected $fillable = [
        'user_id',
        'pgp_pub_key',
        'verification_code',
        'encrypted_message',
        'status',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns the verification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if verification has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if verification can be attempted
     */
    public function canAttempt(): bool
    {
        return $this->status === 'pending'
            && !$this->isExpired()
            && $this->attempts < 5; // Max 5 attempts
    }

    /**
     * Increment failed attempts
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');

        // Mark as failed after max attempts
        if ($this->attempts >= 5) {
            $this->update(['status' => 'failed']);
        }
    }

    /**
     * Mark verification as successful
     */
    public function markAsVerified(): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);
    }

    /**
     * Scope for active verifications
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }
}
