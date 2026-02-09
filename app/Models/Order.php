<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
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
     * @var array<string, string>
     */
    protected $casts = [
        'completed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'early_finalized_at' => 'datetime',
        'dispute_window_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The booting method of the model.
     * Populate the uuid field before creating the model.
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = \Illuminate\Support\Str::uuid();
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName() : string
    {
        return 'uuid';
    }

    /**
     * Get the user that owns the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the listing that owns the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function listing() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get the messages for the order.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserMessage::class);
    }

    /**
     * Get the dispute associated with this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function dispute(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    /**
     * Get the review associated with this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Get the escrow wallet for this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function escrowWallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(EscrowWallet::class);
    }

    /**
     * Get the finalization window for this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function finalizationWindow(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FinalizationWindow::class);
    }

    /**
     * Check if this order has an active dispute.
     *
     * @return bool
     */
    public function hasActiveDispute(): bool
    {
        return $this->dispute()->open()->exists();
    }

    /**
     * Check if this order can have a dispute created.
     *
     * @return bool
     */
    public function canCreateDispute(): bool
    {
        // Early finalized orders can only be disputed within the dispute window
        if ($this->is_early_finalized) {
            return $this->isWithinDisputeWindow() && !$this->dispute()->exists();
        }

        // Regular orders: can only dispute completed orders that don't already have a dispute
        return $this->status === 'completed' && !$this->dispute()->exists();
    }

    /**
     * Check if order qualifies for early finalization.
     *
     * @return bool
     */
    public function canUseEarlyFinalization(): bool
    {
        if ($this->listing->payment_method !== 'direct') {
            return false;
        }

        $category = $this->listing->product->productCategory;
        $vendor = $this->listing->user;

        return $category->canVendorUseEarlyFinalization($vendor);
    }

    /**
     * Check if order is within dispute window.
     *
     * @return bool
     */
    public function isWithinDisputeWindow(): bool
    {
        if (!$this->is_early_finalized || !$this->dispute_window_expires_at) {
            return false;
        }

        return now()->lessThan($this->dispute_window_expires_at);
    }

    /**
     * Check if dispute window has expired.
     *
     * @return bool
     */
    public function isDisputeWindowExpired(): bool
    {
        if (!$this->is_early_finalized || !$this->dispute_window_expires_at) {
            return false;
        }

        return now()->greaterThanOrEqualTo($this->dispute_window_expires_at);
    }

    /**
     * Calculate dispute window expiry time.
     *
     * @param \App\Models\FinalizationWindow $window
     * @return \Carbon\Carbon
     */
    public function calculateDisputeWindowExpiry(FinalizationWindow $window): \Carbon\Carbon
    {
        return now()->addMinutes($window->duration_minutes);
    }

    /**
     * Scope for early finalized orders.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEarlyFinalized($query)
    {
        return $query->where('is_early_finalized', true);
    }

    /**
     * Scope for orders with active dispute window.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisputeWindowActive($query)
    {
        return $query->where('is_early_finalized', true)
                     ->where('dispute_window_expires_at', '>', now());
    }

    /**
     * Scope for orders with expired dispute window.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisputeWindowExpired($query)
    {
        return $query->where('is_early_finalized', true)
                     ->where('dispute_window_expires_at', '<=', now());
    }

    /**
     * Check if escrow funds should be held for this order.
     *
     * @return bool
     */
    public function shouldHoldEscrow(): bool
    {
        return $this->hasActiveDispute() && $this->listing->payment_method === 'escrow';
    }

    /**
     * Check if order has active escrow.
     *
     * @return bool
     */
    public function hasActiveEscrow(): bool
    {
        return $this->escrowWallet && $this->escrowWallet->status === 'active';
    }

    /**
     * Check if escrow is funded.
     *
     * @return bool
     */
    public function isEscrowFunded(): bool
    {
        return $this->escrow_funded_at !== null &&
               $this->escrowWallet &&
               $this->escrowWallet->balance > 0;
    }
}
