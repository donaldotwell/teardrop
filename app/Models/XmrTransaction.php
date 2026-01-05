<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmrTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'xmr_wallet_id',
        'xmr_address_id',
        'txid',
        'payment_id',
        'type',
        'amount',
        'fee',
        'confirmations',
        'unlock_time',
        'height',
        'status',
        'raw_transaction',
        'confirmed_at',
        'unlocked_at',
    ];

    protected $casts = [
        'amount' => 'decimal:12',
        'fee' => 'decimal:12',
        'confirmations' => 'integer',
        'unlock_time' => 'integer',
        'height' => 'integer',
        'raw_transaction' => 'array',
        'confirmed_at' => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    /**
     * Get the wallet that owns the transaction.
     */
    public function wallet()
    {
        return $this->belongsTo(XmrWallet::class, 'xmr_wallet_id');
    }

    /**
     * Get the address associated with the transaction.
     */
    public function address()
    {
        return $this->belongsTo(XmrAddress::class, 'xmr_address_id');
    }

    /**
     * Update transaction confirmations and status.
     */
    public function updateConfirmations(int $confirmations, int $height = null)
    {
        $this->confirmations = $confirmations;

        if ($height) {
            $this->height = $height;
        }

        $minConfirmations = config('monero.min_confirmations', 10);

        // Update status based on confirmations
        if ($confirmations > 0 && $this->status === 'pending') {
            $this->status = 'confirmed';
            $this->confirmed_at = now();
        }

        // Check if unlocked (requires min_confirmations from config)
        if ($confirmations >= $minConfirmations && $this->status === 'confirmed') {
            $this->status = 'unlocked';
            $this->unlocked_at = now();

            // Process confirmation and update internal wallet balance
            $this->processConfirmation();
        }

        $this->save();
    }

    /**
     * Process confirmation - update internal wallet balance.
     */
    public function processConfirmation()
    {
        if ($this->status !== 'unlocked' || $this->type !== 'deposit') {
            return;
        }

        // Update user's internal XMR wallet
        $user = $this->wallet->user;
        $internalWallet = $user->wallets()->where('currency', 'xmr')->first();

        if (!$internalWallet) {
            \Log::warning("No internal XMR wallet found for user {$user->id}");
            return;
        }

        // Create wallet transaction record
        $internalWallet->transactions()->create([
            'amount' => $this->amount,
            'type' => 'deposit',
            'txid' => $this->txid,
            'comment' => "XMR deposit confirmed",
            'confirmed_at' => $this->confirmed_at,
            'completed_at' => now(),
        ]);

        // Update balance
        $internalWallet->increment('balance', $this->amount);

        \Log::info("XMR deposit processed for user {$user->id}: {$this->amount} XMR");
    }

    /**
     * Check if transaction is unlocked and spendable.
     */
    public function isUnlocked(): bool
    {
        return $this->status === 'unlocked';
    }
}
