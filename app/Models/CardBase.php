<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CardBase extends Model
{
    protected $fillable = [
        'vendor_id', 'name', 'price_usd', 'discount_pct',
        'record_count', 'available_count', 'sold_count', 'is_active',
    ];

    protected $casts = [
        'price_usd'       => 'decimal:2',
        'discount_pct'    => 'decimal:2',
        'record_count'    => 'integer',
        'available_count' => 'integer',
        'sold_count'      => 'integer',
        'is_active'       => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** Price after discount */
    public function getEffectivePriceAttribute(): float
    {
        $pct = max(0, min(99, (float) $this->discount_pct));
        return round((float) $this->price_usd * (1 - $pct / 100), 2);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(Card::class, 'base_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(CardPurchase::class, 'base_id');
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
