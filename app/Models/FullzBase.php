<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FullzBase extends Model
{
    protected $fillable = [
        'vendor_id', 'name', 'price_usd',
        'record_count', 'available_count', 'sold_count', 'is_active',
    ];

    protected $casts = [
        'price_usd'       => 'decimal:2',
        'record_count'    => 'integer',
        'available_count' => 'integer',
        'sold_count'      => 'integer',
        'is_active'       => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(Fullz::class, 'base_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(FullzPurchase::class, 'base_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('available_count', '>', 0);
    }

    public function totalRevenue(): float
    {
        return (float) $this->purchases()->sum('total_usd');
    }
}
