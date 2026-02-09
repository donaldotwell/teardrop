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
     * Generate a new subaddress for this wallet.
     */
    public function generateNewAddress(): XmrAddress
    {
        $repository = new \App\Repositories\MoneroRepository();

        // Create new subaddress in master wallet
        $subaddressData = $repository->rpcCall('create_address', [
            'account_index' => 0,
            'label' => "User {$this->user_id} - Address " . (time()),
        ]);

        if (!$subaddressData || !isset($subaddressData['address'])) {
            throw new \Exception("Failed to generate Monero subaddress from RPC");
        }

        $address = $this->addresses()->create([
            'address' => $subaddressData['address'],
            'account_index' => 0,
            'address_index' => $subaddressData['address_index'],
            'label' => "User {$this->user_id} - Address " . (time()),
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
     */
    public function updateBalance()
    {
        try {
            // Get actual balance from monero-wallet-rpc for this subaddress
            $balanceData = \App\Repositories\MoneroRepository::getBalance($this->name);

            if ($balanceData) {
                $this->update([
                    'balance' => $balanceData['balance'],
                    'unlocked_balance' => $balanceData['unlocked_balance'],
                ]);
                
                \Log::debug("Updated wallet {$this->id} from RPC: balance={$balanceData['balance']}, unlocked={$balanceData['unlocked_balance']}");
            }

            // Update statistics from transaction records
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
     * Get accurate balance from transaction records (not stale RPC balance).
     * 
     * @return array ['balance' => float, 'unlocked_balance' => float]
     */
    public function getBalance(): array
    {
        $minConfirmations = config('monero.min_confirmations', 10);
        
        // Sum all confirmed deposit transactions
        $totalIncoming = $this->transactions()
            ->where('type', 'deposit')
            ->whereIn('status', ['confirmed', 'unlocked'])
            ->sum('amount');
        
        // Sum all outgoing transactions
        $totalOutgoing = $this->transactions()
            ->where('type', 'withdrawal')
            ->where('status', '!=', 'failed')
            ->sum('amount');
        
        // Total balance
        $balance = $totalIncoming - abs($totalOutgoing);
        
        // Unlocked balance (only fully confirmed transactions)
        $unlockedIncoming = $this->transactions()
            ->where('type', 'deposit')
            ->where('status', 'unlocked')
            ->where('confirmations', '>=', $minConfirmations)
            ->sum('amount');
        
        $unlocked_balance = $unlockedIncoming - abs($totalOutgoing);
        
        return [
            'balance' => max(0, $balance),
            'unlocked_balance' => max(0, $unlocked_balance),
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
     * Get QR code data for the primary address.
     */
    public function getQrCodeData(): string
    {
        return "monero:" . $this->primary_address;
    }
}
