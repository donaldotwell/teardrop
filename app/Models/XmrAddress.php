<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmrAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'xmr_wallet_id',
        'address',
        'account_index',
        'address_index',
        'label',
        'balance',
        'total_received',
        'tx_count',
        'first_used_at',
        'last_used_at',
        'is_used',
        'last_synced_height',
        'last_synced_at',
    ];

    protected $casts = [
        'account_index' => 'integer',
        'address_index' => 'integer',
        'balance' => 'decimal:12',
        'total_received' => 'decimal:12',
        'tx_count' => 'integer',
        'first_used_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Boot method - Set up event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // When a new address is created, mark previous addresses as used
        static::created(function ($address) {
            // Mark all previous addresses for this wallet as used (except the newly created one)
            static::where('xmr_wallet_id', $address->xmr_wallet_id)
                ->where('id', '!=', $address->id)
                ->where('is_used', false)
                ->update(['is_used' => true]);
        });
    }

    /**
     * Get the wallet that owns the address.
     */
    public function wallet()
    {
        return $this->belongsTo(XmrWallet::class, 'xmr_wallet_id');
    }

    /**
     * Get the transactions for the address.
     */
    public function transactions()
    {
        return $this->hasMany(XmrTransaction::class);
    }

    /**
     * Mark the address as used.
     */
    public function markAsUsed()
    {
        $this->update([
            'is_used' => true,
            'first_used_at' => $this->first_used_at ?? now(),
            'last_used_at' => now(),
        ]);
    }

    /**
     * Get QR code data for this address.
     */
    public function getQrCodeData(): string
    {
        return "monero:" . $this->address;
    }
}
