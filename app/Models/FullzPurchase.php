<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FullzPurchase extends Model
{
    protected $fillable = [
        'buyer_id', 'vendor_id', 'base_id',
        'currency', 'total_usd', 'total_crypto', 'txid', 'record_count',
    ];

    protected $casts = [
        'total_usd'    => 'decimal:2',
        'total_crypto' => 'decimal:12',
        'record_count' => 'integer',
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

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(FullzBase::class, 'base_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(Fullz::class, 'purchase_id');
    }
}
