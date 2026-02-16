<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmrWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'primary_address',
        'view_key',
        'spend_key_encrypted',
        'seed_encrypted',
        'password_encrypted',
        'height',
        'balance',
        'unlocked_balance',
        'total_received',
        'total_sent',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'height' => 'integer',
        'balance' => 'decimal:12',
        'unlocked_balance' => 'decimal:12',
        'total_received' => 'decimal:12',
        'total_sent' => 'decimal:12',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Hidden attributes (password should never be serialized).
     */
    protected $hidden = [
        'password_encrypted',
        'spend_key_encrypted',
        'seed_encrypted',
    ];

    /**
     * Get the user that owns the wallet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the addresses for the wallet.
     */
    public function addresses()
    {
        return $this->hasMany(XmrAddress::class);
    }

    /**
     * Get the transactions for the wallet.
     */
    public function transactions()
    {
        return $this->hasMany(XmrTransaction::class);
    }

    /**
     * Get the current unused address for receiving funds.
     */
    public function getCurrentAddress(): ?XmrAddress
    {
        return $this->addresses()
            ->where('is_used', false)
            ->orderBy('address_index')
            ->first();
    }

    /**
     * Decrypt and return the wallet password for RPC operations.
     *
     * @return string Plaintext wallet password
     * @throws \RuntimeException If no encrypted password is stored
     */
    public function getDecryptedPassword(): string
    {
        if (empty($this->password_encrypted)) {
            throw new \RuntimeException("No encrypted password stored for wallet {$this->id}");
        }

        return \Illuminate\Support\Facades\Crypt::decryptString($this->password_encrypted);
    }

    /**
     * Generate a new subaddress for this wallet.
     * Opens the wallet file on RPC, creates a subaddress, and closes it.
     */
    public function generateNewAddress(): XmrAddress
    {
        $repository = new \App\Repositories\MoneroRepository();

        $label = "User {$this->user_id} - Address " . time();

        // Opens this wallet file on RPC, creates subaddress, closes it
        $subaddressData = $repository->createAddress($this, $label);

        $address = $this->addresses()->create([
            'address' => $subaddressData['address'],
            'account_index' => 0,
            'address_index' => $subaddressData['address_index'],
            'label' => $label,
            'balance' => 0,
            'total_received' => 0,
            'tx_count' => 0,
            'is_used' => false,
        ]);

        \Log::debug("Generated new Monero subaddress for wallet {$this->id}", [
            'address_index' => $subaddressData['address_index'],
            'address' => $subaddressData['address'],
        ]);

        return $address;
    }

    /**
     * Update wallet balance from blockchain.
     * Opens the per-user wallet file, queries RPC, and writes authoritative values to DB.
     */
    public function updateBalance()
    {
        try {
            $repository = new \App\Repositories\MoneroRepository();
            $balanceData = $repository->getWalletBalance($this);

            $this->update([
                'balance' => $balanceData['balance'],
                'unlocked_balance' => $balanceData['unlocked_balance'],
                'last_synced_at' => now(),
            ]);

            \Log::debug("Updated wallet {$this->id} from RPC: balance={$balanceData['balance']}, unlocked={$balanceData['unlocked_balance']}");

            // Update statistics from transaction records
            $deposits = $this->transactions()
                ->where('type', 'deposit')
                ->where('status', 'unlocked')
                ->sum('amount');

            $withdrawals = $this->transactions()
                ->where('type', 'withdrawal')
                ->whereIn('status', ['confirmed', 'unlocked', 'pending'])
                ->sum('amount');

            $this->update([
                'total_received' => $deposits,
                'total_sent' => $withdrawals,
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to update XMR wallet balance for wallet {$this->id}: " . $e->getMessage());
        }
    }

    /**
     * Get wallet balance from cached DB columns (set by syncWallet() during sync).
     *
     * Returns the authoritative balance from the last sync cycle.
     * This avoids RPC calls — safe for middleware / page loads.
     *
     * For critical financial operations (order placement, withdrawals),
     * use getRpcBalance() to get a live on-chain balance.
     *
     * @return array ['balance' => float, 'unlocked_balance' => float]
     */
    public function getBalance(): array
    {
        return [
            'balance' => (float) $this->balance,
            'unlocked_balance' => (float) $this->unlocked_balance,
        ];
    }

    /**
     * Get total received from all incoming transactions.
     *
     * @return float Total received in XMR
     */
    public function getTotalReceived(): float
    {
        return $this->transactions()
            ->where('type', 'deposit')
            ->whereIn('status', ['confirmed', 'unlocked'])
            ->sum('amount');
    }

    /**
     * Get total sent from all withdrawal transactions.
     *
     * @return float Total sent in XMR
     */
    public function getTotalSent(): float
    {
        return abs($this->transactions()
            ->where('type', 'withdrawal')
            ->where('status', '!=', 'failed')
            ->sum('amount'));
    }

    /**
     * Get live balance from Monero RPC by opening the per-user wallet file.
     * Use this for critical financial operations (order placement, withdrawals)
     * where the DB balance may be stale.
     *
     * @return array ['balance' => float, 'unlocked_balance' => float]
     */
    public function getRpcBalance(): array
    {
        try {
            $repository = new \App\Repositories\MoneroRepository();
            return $repository->getWalletBalance($this);
        } catch (\Exception $e) {
            \Log::error("Failed to get RPC balance for wallet {$this->id}: " . $e->getMessage());
            return ['balance' => 0, 'unlocked_balance' => 0];
        }
    }

    /**
     * Get QR code data for the primary address.
     */
    public function getQrCodeData(): string
    {
        return "monero:" . $this->primary_address;
    }
}
