<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BtcAddress extends Model
{
    protected $fillable = [
        'btc_wallet_id',
        'address',
        'address_index',
        'balance',
        'total_received',
        'tx_count',
        'first_used_at',
        'last_used_at',
        'is_used'
    ];

    protected $casts = [
        'balance' => 'decimal:8',
        'total_received' => 'decimal:8',
        'address_index' => 'integer',
        'tx_count' => 'integer',
        'first_used_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_used' => 'boolean'
    ];

    /**
     * Get the wallet that owns this address.
     */
    public function btcWallet(): BelongsTo
    {
        return $this->belongsTo(BtcWallet::class);
    }

    /**
     * Get all transactions for this address.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(BtcTransaction::class);
    }

    /**
     * Mark this address as used.
     */
    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'first_used_at' => $this->first_used_at ?? now(),
            'last_used_at' => now()
        ]);
    }

    /**
     * Update address balance and transaction count.
     */
    public function updateStats(): void
    {
        $confirmedTransactions = $this->transactions()->where('status', 'confirmed');

        $this->update([
            'total_received' => $confirmedTransactions->where('type', 'deposit')->sum('amount'),
            'tx_count' => $confirmedTransactions->count(),
            'last_used_at' => now()
        ]);

        // Calculate current balance
        $deposits = $confirmedTransactions->where('type', 'deposit')->sum('amount');
        $withdrawals = $confirmedTransactions->where('type', 'withdrawal')->sum('amount');

        $this->update(['balance' => $deposits - $withdrawals]);
    }

    /**
     * Get QR code data for this address.
     */
    public function getQrCodeData(): string
    {
        return "bitcoin:{$this->address}";
    }

    /**
     * Check if address has pending transactions.
     */
    public function hasPendingTransactions(): bool
    {
        return $this->transactions()->where('status', 'pending')->exists();
    }

    /**
     * Get the latest transaction for this address.
     */
    public function getLatestTransaction(): ?BtcTransaction
    {
        return $this->transactions()->latest()->first();
    }
}
