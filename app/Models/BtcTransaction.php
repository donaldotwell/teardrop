<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BtcTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'btc_wallet_id',
        'btc_address_id',
        'txid',
        'type',
        'amount',
        'usd_value',
        'fee',
        'confirmations',
        'status',
        'raw_transaction',
        'block_hash',
        'block_height',
        'confirmed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'usd_value' => 'decimal:2',
        'fee' => 'decimal:8',
        'confirmations' => 'integer',
        'block_height' => 'integer',
        'raw_transaction' => 'array',
        'confirmed_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the wallet that owns this transaction.
     */
    public function btcWallet(): BelongsTo
    {
        return $this->belongsTo(BtcWallet::class);
    }

    /**
     * Get the address associated with this transaction.
     */
    public function btcAddress(): BelongsTo
    {
        return $this->belongsTo(BtcAddress::class);
    }

    /**
     * Check if transaction is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed' && $this->confirmations >= 1;
    }

    /**
     * Check if transaction is fully confirmed (6+ confirmations).
     */
    public function isFullyConfirmed(): bool
    {
        return $this->status === 'confirmed' && $this->confirmations >= 6;
    }

    /**
     * Update confirmation status.
     */
    public function updateConfirmations(int $confirmations, ?string $blockHash = null, ?int $blockHeight = null): void
    {
        $oldStatus = $this->status;
        $status = $confirmations > 0 ? 'confirmed' : 'pending';

        $this->update([
            'confirmations' => $confirmations,
            'status' => $status,
            'block_hash' => $blockHash,
            'block_height' => $blockHeight,
            'confirmed_at' => $confirmations > 0 ? ($this->confirmed_at ?? now()) : null
        ]);

        // Only process confirmation on first confirmation (status change from pending to confirmed)
        if ($oldStatus === 'pending' && $status === 'confirmed') {
            $this->processConfirmation();
        }
    }

    /**
     * Process transaction confirmation.
     */
    public function processConfirmation(): void
    {
        if ($this->type === 'deposit') {
            // Mark address as used
            $this->btcAddress?->markAsUsed();

            // Update address stats
            $this->btcAddress?->updateStats();

            // Automatically generate new address for next deposit (privacy enhancement)
            if ($this->btcAddress && !$this->btcWallet->getCurrentAddress()) {
                try {
                    $this->btcWallet->generateNewAddress();
                    \Log::info("Auto-generated new address for wallet {$this->btcWallet->id} after deposit");
                } catch (\Exception $e) {
                    \Log::error("Failed to auto-generate address for wallet {$this->btcWallet->id}: " . $e->getMessage());
                }
            }

            // Update wallet balance
            $this->btcWallet->updateBalance();

            // Create main wallet transaction (only once)
            $this->createMainWalletTransaction();
        }

        if ($this->type === 'withdrawal') {
            // Update wallet balance for withdrawals
            $this->btcWallet->updateBalance();

            // Create main wallet transaction for withdrawal
            $this->createMainWalletTransaction();
        }
    }

    /**
     * Create corresponding transaction in main wallet system.
     */
    private function createMainWalletTransaction(): void
    {
        $mainWallet = $this->btcWallet->user->wallets()->where('currency', 'btc')->first();

        if (!$mainWallet) {
            return;
        }

        // CRITICAL: Check if transaction already exists to prevent double-entry
        $existingWalletTx = $mainWallet->transactions()
            ->where('txid', $this->txid)
            ->exists();

        if ($existingWalletTx) {
            \Log::warning("Wallet transaction already exists for txid: {$this->txid}");
            return;
        }

        // Determine amount sign based on type
        $amount = $this->type === 'deposit' ? $this->amount : -$this->amount;

        // Create the wallet transaction
        $mainWallet->transactions()->create([
            'amount' => $amount,
            'type' => $this->type,
            'txid' => $this->txid,
            'comment' => $this->type === 'deposit'
                ? "Bitcoin deposit"
                : "Bitcoin withdrawal",
            'confirmed_at' => $this->confirmed_at,
            'completed_at' => $this->confirmed_at
        ]);

        \Log::info("Created main wallet transaction for txid: {$this->txid}");
    }

    /**
     * Get transaction explorer URL.
     */
    public function getExplorerUrlAttribute(): string
    {
        return "https://blockstream.info/tx/{$this->txid}";
    }

    /**
     * Get formatted amount with symbol.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 8) . ' BTC';
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'confirmed' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            default => 'gray'
        };
    }
}
