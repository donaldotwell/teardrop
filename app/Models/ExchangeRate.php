<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'crypto_name',
        'crypto_shortname',
        'usd_rate',
    ];

    protected $casts = [
        'usd_rate' => 'decimal:8',
    ];

    /**
     * Get exchange rate for a specific crypto by shortname.
     */
    public static function getRate(string $cryptoShortname): ?float
    {
        $rate = self::where('crypto_shortname', $cryptoShortname)->first();
        return $rate ? (float) $rate->usd_rate : null;
    }

    /**
     * Get all current exchange rates.
     */
    public static function getAllRates(): array
    {
        return self::all()->pluck('usd_rate', 'crypto_shortname')->toArray();
    }
}
