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
        'password_hash',
        'height',
        'balance',
        'unlocked_balance',
        'total_received',
        'total_sent',
        'is_active',
    ];

    protected $casts = [
        'height' => 'integer',
        'balance' => 'decimal:12',
        'unlocked_balance' => 'decimal:12',
        'total_received' => 'decimal:12',
        'total_sent' => 'decimal:12',
        'is_active' => 'boolean',
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
     * Get the current unused address or primary address.
     */
    public function getCurrentAddress()
    {
        // For Monero, we can return the primary address or an unused subaddress
        // Primary address is always valid for receiving
        return $this->addresses()
            ->where('is_used', false)
            ->orderBy('address_index')
            ->first() ?? $this->addresses()->where('address_index', 0)->first();
    }

    /**
     * Generate a new subaddress for the wallet.
     */
    public function generateNewAddress(string $label = null)
    {
        $nextIndex = $this->addresses()->max('address_index') + 1;

        // Call MoneroRepository to generate subaddress via RPC
        $addressData = \App\Repositories\MoneroRepository::createSubaddress($this->name, 0, $label);

        if (!$addressData) {
            throw new \Exception("Failed to generate Monero subaddress");
        }

        return $this->addresses()->create([
            'address' => $addressData['address'],
            'account_index' => 0,
            'address_index' => $addressData['address_index'],
            'label' => $label,
            'balance' => 0,
            'total_received' => 0,
            'tx_count' => 0,
            'is_used' => false,
        ]);
    }

    /**
     * Update wallet balance from blockchain.
     */
    public function updateBalance()
    {
        try {
            $balanceData = \App\Repositories\MoneroRepository::getBalance($this->name);

            if ($balanceData) {
                $this->update([
                    'balance' => $balanceData['balance'],
                    'unlocked_balance' => $balanceData['unlocked_balance'],
                ]);
            }

            // Update totals from transactions
            $deposits = $this->transactions()
                ->where('type', 'deposit')
                ->where('status', 'unlocked')
                ->sum('amount');

            $withdrawals = $this->transactions()
                ->where('type', 'withdrawal')
                ->whereIn('status', ['confirmed', 'unlocked'])
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
     * Get QR code data for the primary address.
     */
    public function getQrCodeData(): string
    {
        return "monero:" . $this->primary_address;
    }
}
