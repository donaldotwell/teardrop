<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fullz extends Model
{
    protected $table = 'fullz';

    protected $fillable = [
        'base_id', 'vendor_id',
        'name', 'address', 'city', 'state', 'zip', 'phone_no', 'gender', 'ssn', 'dob',
        'status', 'buyer_id', 'purchase_id', 'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public function base(): BelongsTo
    {
        return $this->belongsTo(FullzBase::class, 'base_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(FullzPurchase::class, 'purchase_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
