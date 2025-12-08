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
     * Get the listings associated with the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function listings() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Listing::class);
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
        // Can only dispute completed orders that don't already have a dispute
        return $this->status === 'completed' && !$this->dispute()->exists();
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
}
