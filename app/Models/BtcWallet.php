<?php

namespace App\Models;

use App\Repositories\BitcoinRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BtcWallet extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'xpub',
        'address_index',
        'total_received',
        'total_sent',
        'balance',
        'is_active'
    ];

    protected $casts = [
        'total_received' => 'decimal:8',
        'total_sent' => 'decimal:8',
        'balance' => 'decimal:8',
        'address_index' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all addresses for this wallet.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(BtcAddress::class);
    }

    /**
     * Get all transactions for this wallet.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(BtcTransaction::class);
    }

    /**
     * Get the current unused address for receiving funds.
     */
    public function getCurrentAddress(): ?BtcAddress
    {
        return $this->addresses()
            ->where('is_used', false)
            ->orderBy('address_index')
            ->first();
    }

    /**
     * Generate a new address for this wallet.
     */
    public function generateNewAddress(): BtcAddress
    {
        $repository = new BitcoinRepository();

        // Ensure Bitcoin wallet exists on the node
        $repository->generateBTCWallet($this->user->username_pri);

        // Get new address from Bitcoin node
        $newAddress = $repository->getNewAddress($this->user->username_pri);

        // If address generation failed, throw exception
        if (!$newAddress) {
            throw new \Exception("Failed to generate Bitcoin address from node");
        }

        // Get next address index
        $nextIndex = $this->addresses()->max('address_index') + 1;

        $address = $this->addresses()->create([
            'address' => $newAddress,
            'address_index' => $nextIndex,
            'is_used' => false
        ]);

        // Update the wallet's current address index
        $this->update(['address_index' => $nextIndex]);

        return $address;
    }

    /**
     * Generate Bitcoin address from index (placeholder implementation).
     * You'll need to implement actual HD wallet address generation here.
     */
    private function generateAddressFromIndex(int $index): string
    {
        // This is a placeholder implementation
        // In real implementation, you would use the xpub and derive the address
        return 'bc1' . str_pad($index, 39, '0', STR_PAD_LEFT);
    }

    /**
     * Update wallet balance and sync with main wallet.
     */
    public function updateBalance(): void
    {
        $this->refresh();

        // Calculate balance from confirmed transactions
        $totalReceived = $this->transactions()
            ->where('type', 'deposit')
            ->where('status', 'confirmed')
            ->sum('amount');

        // For withdrawals, include both confirmed AND pending (to prevent double-spend)
        $totalSent = $this->transactions()
            ->where('type', 'withdrawal')
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('amount');

        $this->update([
            'total_received' => $totalReceived,
            'total_sent' => $totalSent,
            'balance' => $totalReceived - $totalSent
        ]);

        // Sync with main wallet system (only for user wallets, not escrow wallets)
        if ($this->user_id && $this->user) {
            $mainWallet = $this->user->wallets()->where('currency', 'btc')->first();
            if ($mainWallet) {
                $mainWallet->update(['balance' => $this->balance]);
            }
        }
    }

    /**
     * Get wallet formatted display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return "BTC Wallet ({$this->name})";
    }
}
