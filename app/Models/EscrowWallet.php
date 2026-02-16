<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscrowWallet extends Model
{
    protected $fillable = [
        'order_id',
        'currency',
        'wallet_name',
        'wallet_password_encrypted',
        'address',
        'balance',
        'status',
        'released_at',
    ];

    /**
     * Hidden attributes.
     */
    protected $hidden = [
        'wallet_password_encrypted',
    ];

    protected $casts = [
        'balance' => 'decimal:12',
        'released_at' => 'datetime',
    ];

    /**
     * Get the order this escrow wallet belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if wallet can be released.
     */
    public function canRelease(): bool
    {
        return $this->status === 'active' && $this->balance > 0;
    }

    /**
     * Check if wallet can be refunded.
     */
    public function canRefund(): bool
    {
        return $this->status === 'active' && $this->balance > 0;
    }

    /**
     * Mark wallet as released.
     */
    public function markAsReleased(): void
    {
        $this->update([
            'status' => 'released',
            'released_at' => now(),
        ]);
    }

    /**
     * Mark wallet as refunded.
     */
    public function markAsRefunded(): void
    {
        $this->update([
            'status' => 'refunded',
            'released_at' => now(),
        ]);
    }

    /**
     * Update balance from blockchain.
     */
    public function updateBalance(): void
    {
        if ($this->currency === 'btc') {
            $this->updateBitcoinBalance();
        } elseif ($this->currency === 'xmr') {
            $this->updateMoneroBalance();
        }
    }

    /**
     * Update Bitcoin balance from blockchain.
     */
    private function updateBitcoinBalance(): void
    {
        $btcWallet = BtcWallet::where('name', $this->wallet_name)->first();
        if ($btcWallet) {
            $btcWallet->updateBalance();
            $this->update(['balance' => $btcWallet->balance]);
        }
    }

    /**
     * Update Monero balance from blockchain.
     * Per-wallet architecture: look up by wallet_name (unique per escrow).
     */
    private function updateMoneroBalance(): void
    {
        $xmrWallet = XmrWallet::where('name', $this->wallet_name)->first();
        if ($xmrWallet) {
            $xmrWallet->updateBalance();
            $this->update(['balance' => $xmrWallet->unlocked_balance]);
        }
    }
}
