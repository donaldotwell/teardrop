<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmrTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'xmr_wallet_id',
        'xmr_address_id',
        'txid',
        'payment_id',
        'type',
        'amount',
        'usd_value',
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
        'usd_value' => 'decimal:2',
        'fee' => 'decimal:12',
        'confirmations' => 'integer',
        'unlock_time' => 'integer',
        'height' => 'integer',
        'raw_transaction' => 'array',
        'confirmed_at' => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

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
    public function updateConfirmations(int $confirmations, ?int $height = null)
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

        // CRITICAL: Check GLOBALLY if wallet transaction exists to prevent duplicates
        // For internal transfers between subaddresses, same txid creates multiple XmrTransaction records
        // but should only create ONE WalletTransaction (for the actual recipient)
        $existingWalletTx = \App\Models\WalletTransaction::where('txid', $this->txid)->first();

        if ($existingWalletTx) {
            \Log::debug("Wallet transaction already exists globally for txid {$this->txid}, skipping for user {$user->id}");
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

        // Check if this is a featured listing payment (for withdrawals)
        $this->processFeaturedListingPayment();
    }

    /**
     * Process featured listing payment after confirmation.
     */
    private function processFeaturedListingPayment(): void
    {
        // Check if this transaction is for featuring a listing
        if (!is_array($this->raw_transaction)) {
            return;
        }

        $purpose = $this->raw_transaction['purpose'] ?? null;
        $listingId = $this->raw_transaction['listing_id'] ?? null;

        if ($purpose !== 'feature_listing' || !$listingId) {
            return;
        }

        \Log::info("[FEATURED LISTING XMR] Processing featured listing payment - Txid: {$this->txid}, Listing ID: {$listingId}, Amount: {$this->amount} XMR, Fee USD: " . ($this->raw_transaction['fee_usd'] ?? 'N/A'));

        // Find the listing
        $listing = \App\Models\Listing::find($listingId);

        if (!$listing) {
            \Log::warning("[FEATURED LISTING XMR] Featured listing payment confirmed but listing not found: {$listingId} (txid: {$this->txid})");
            return;
        }

        // XMR payments are marked as featured immediately in controller, but log it here for tracking
        if ($listing->is_featured) {
            \Log::info("[FEATURED LISTING XMR] ✓ Listing {$listingId} ('{$listing->title}') confirmed as featured (txid: {$this->txid}, vendor_id: {$listing->user_id}, status: {$this->status})");
        } else {
            \Log::warning("[FEATURED LISTING XMR] Listing {$listingId} payment confirmed but not marked as featured - marking now (txid: {$this->txid})");
            $listing->update(['is_featured' => true]);
        }
    }

    /**
     * Check if transaction is unlocked and spendable.
     */
    public function isUnlocked(): bool
    {
        return $this->status === 'unlocked';
    }
}
