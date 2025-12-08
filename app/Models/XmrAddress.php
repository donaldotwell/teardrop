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
