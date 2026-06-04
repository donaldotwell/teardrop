<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    protected $fillable = [
        'base_id', 'vendor_id',
        'card_number', 'exp_month', 'exp_year', 'cvv', 'cardholder_name',
        'address', 'city', 'state', 'zip', 'country', 'email', 'phone',
        'price_usd', 'status', 'buyer_id', 'purchase_id', 'sold_at',
    ];

    protected $casts = [
        'price_usd' => 'decimal:2',
        'sold_at'   => 'datetime',
    ];

    public function base(): BelongsTo
    {
        return $this->belongsTo(CardBase::class, 'base_id');
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
        return $this->belongsTo(CardPurchase::class, 'purchase_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /** BIN = first 6 digits */
    public function getBinAttribute(): string
    {
        return substr(preg_replace('/\D/', '', $this->card_number), 0, 6);
    }

    /** Masked display: first 6 + *** + last 4 */
    public function getMaskedNumberAttribute(): string
    {
        $digits = preg_replace('/\D/', '', $this->card_number);
        $len    = strlen($digits);
        if ($len <= 10) {
            return $digits;
        }
        return substr($digits, 0, 6) . str_repeat('*', $len - 10) . substr($digits, -4);
    }

    public function getCardTypeAttribute(): string
    {
        $num = preg_replace('/\D/', '', $this->card_number);
        if (str_starts_with($num, '4')) {
            return 'VISA';
        }
        if (preg_match('/^5[1-5]/', $num) || preg_match('/^2(2[2-9][1-9]|[3-6]\d{2}|7[01]\d|720)/', $num)) {
            return 'MC';
        }
        if (preg_match('/^3[47]/', $num)) {
            return 'AMEX';
        }
        if (preg_match('/^(6011|65|64[4-9]|622)/', $num)) {
            return 'DISC';
        }
        return 'CARD';
    }
}
