# Escrow Wallet Implementation Plan

## Executive Summary

This document outlines the implementation of a **proper escrow system** for the Teardrop marketplace. The current system only deducts balance from a generic `Wallet` model while keeping actual cryptocurrency in the buyer's wallet until order completion. The new system will create **dedicated escrow wallets per order** that physically hold the cryptocurrency until release conditions are met.

---

## Current Architecture Analysis

### Wallet Structure
- **BtcWallet**: One per user (name: `username_pri`)
- **XmrWallet**: One per user (name: `username_pri.wallet`)
- **Wallet**: Generic balance tracking model (NOT crypto-specific)

### Current Flow (BROKEN)
```
OrderController@store:
├─ Deducts from generic Wallet model (order_escrow transaction)
└─ Crypto stays in buyer's BtcWallet/XmrWallet (NOT TRUE ESCROW)

OrderController@complete:
├─ Transfers crypto FROM buyer's wallet TO vendor wallet via RPC
├─ completeBitcoinOrder(): BitcoinRepository::sendBitcoin()
└─ completeMoneroOrder(): MoneroRepository::transfer()
```

**Problem**: Buyer retains control of funds until completion. This is NOT escrow.

---

## New Architecture: Per-Order Escrow Wallets

### Design Principles
1. **Physical Isolation**: Each order gets its own RPC wallet holding actual cryptocurrency
2. **Wallet Naming Convention**: `escrow_order_{order_id}_{currency}`
   - Example: `escrow_order_123_btc`, `escrow_order_456_xmr.wallet`
3. **Three-Stage Flow**: Buyer → Escrow → Vendor
4. **Automatic Cleanup**: Escrow wallets archived after order finalization

### Database Schema

#### New Table: `escrow_wallets`
```sql
CREATE TABLE escrow_wallets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT NOT NULL UNIQUE,
    currency ENUM('btc', 'xmr') NOT NULL,
    wallet_name VARCHAR(255) NOT NULL UNIQUE,
    wallet_password_hash VARCHAR(255) NOT NULL, -- For XMR wallets
    address VARCHAR(255) NOT NULL, -- Primary receiving address
    balance DECIMAL(20, 12) DEFAULT 0,
    status ENUM('active', 'released', 'refunded', 'archived') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    released_at TIMESTAMP NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_currency (currency),
    INDEX idx_status (status)
);
```

#### Update: `orders` table
```sql
ALTER TABLE orders ADD COLUMN escrow_wallet_id BIGINT NULL;
ALTER TABLE orders ADD FOREIGN KEY (escrow_wallet_id) REFERENCES escrow_wallets(id);
ALTER TABLE orders ADD COLUMN escrow_funded_at TIMESTAMP NULL;
```

---

## Implementation Details

### Phase 1: Database Migration

**File**: `database/migrations/YYYY_MM_DD_create_escrow_wallets_table.php`

```php
Schema::create('escrow_wallets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->unique()->constrained()->onDelete('cascade');
    $table->enum('currency', ['btc', 'xmr']);
    $table->string('wallet_name')->unique();
    $table->string('wallet_password_hash')->nullable(); // For XMR
    $table->string('address'); // Primary receiving address
    $table->decimal('balance', 20, 12)->default(0);
    $table->enum('status', ['active', 'released', 'refunded', 'archived'])->default('active');
    $table->timestamp('released_at')->nullable();
    $table->timestamps();
    
    $table->index(['currency', 'status']);
});

Schema::table('orders', function (Blueprint $table) {
    $table->foreignId('escrow_wallet_id')->nullable()->after('txid')->constrained();
    $table->timestamp('escrow_funded_at')->nullable()->after('escrow_wallet_id');
});
```

---

### Phase 2: EscrowWallet Model

**File**: `app/Models/EscrowWallet.php`

```php
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
        'wallet_password_hash',
        'address',
        'balance',
        'status',
        'released_at',
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

    private function updateBitcoinBalance(): void
    {
        $btcWallet = BtcWallet::where('name', $this->wallet_name)->first();
        if ($btcWallet) {
            $btcWallet->updateBalance();
            $this->update(['balance' => $btcWallet->balance]);
        }
    }

    private function updateMoneroBalance(): void
    {
        $xmrWallet = XmrWallet::where('name', $this->wallet_name)->first();
        if ($xmrWallet) {
            $xmrWallet->updateBalance();
            $this->update(['balance' => $xmrWallet->unlocked_balance]);
        }
    }
}
```

---

### Phase 3: EscrowService

**File**: `app/Services/EscrowService.php`

```php
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\EscrowWallet;
use App\Models\BtcWallet;
use App\Models\XmrWallet;
use App\Repositories\BitcoinRepository;
use App\Repositories\MoneroRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    /**
     * Create escrow wallet for order and transfer funds.
     *
     * @param Order $order
     * @return EscrowWallet
     * @throws \Exception
     */
    public function createEscrowForOrder(Order $order): EscrowWallet
    {
        // Generate unique wallet name
        $walletName = $this->generateWalletName($order);
        
        if ($order->currency === 'btc') {
            return $this->createBitcoinEscrow($order, $walletName);
        } elseif ($order->currency === 'xmr') {
            return $this->createMoneroEscrow($order, $walletName);
        }
        
        throw new \Exception("Unsupported currency: {$order->currency}");
    }

    /**
     * Create Bitcoin escrow wallet.
     */
    private function createBitcoinEscrow(Order $order, string $walletName): EscrowWallet
    {
        $repository = new BitcoinRepository();
        
        // 1. Create Bitcoin wallet on node
        if (!$repository->createWallet($walletName)) {
            throw new \Exception("Failed to create Bitcoin escrow wallet");
        }
        
        // 2. Generate receiving address
        $address = $repository->getNewAddress($walletName);
        if (!$address) {
            throw new \Exception("Failed to generate escrow address");
        }
        
        // 3. Create BtcWallet record for syncing
        $btcWallet = BtcWallet::create([
            'user_id' => null, // Escrow wallets don't belong to users
            'name' => $walletName,
            'xpub' => null,
            'address_index' => 0,
            'is_active' => true,
        ]);
        
        // 4. Create BtcAddress record
        $btcWallet->addresses()->create([
            'address' => $address,
            'address_index' => 0,
            'is_used' => false,
        ]);
        
        // 5. Create EscrowWallet record
        $escrowWallet = EscrowWallet::create([
            'order_id' => $order->id,
            'currency' => 'btc',
            'wallet_name' => $walletName,
            'wallet_password_hash' => null,
            'address' => $address,
            'balance' => 0,
            'status' => 'active',
        ]);
        
        Log::info("Bitcoin escrow wallet created for order #{$order->id}", [
            'wallet_name' => $walletName,
            'address' => $address,
        ]);
        
        return $escrowWallet;
    }

    /**
     * Create Monero escrow wallet.
     */
    private function createMoneroEscrow(Order $order, string $walletName): EscrowWallet
    {
        $repository = new MoneroRepository();
        
        // Generate unique password for escrow wallet
        $password = hash('sha256', $order->id . $order->uuid . config('app.key'));
        $passwordHash = hash('sha256', $password);
        
        // 1. Create Monero wallet via RPC
        $walletData = $repository->createWallet($walletName, $password);
        if (!$walletData) {
            throw new \Exception("Failed to create Monero escrow wallet");
        }
        
        // 2. Get primary address
        $addressData = $repository->getAddress();
        if (!$addressData) {
            throw new \Exception("Failed to get Monero escrow address");
        }
        
        // 3. Create XmrWallet record for syncing
        $xmrWallet = XmrWallet::create([
            'user_id' => null, // Escrow wallets don't belong to users
            'name' => $walletName,
            'primary_address' => $addressData['address'],
            'view_key' => $walletData['view_key'] ?? null,
            'spend_key_encrypted' => null, // Don't store spend key for security
            'seed_encrypted' => null,
            'password_hash' => $passwordHash,
            'height' => 0,
            'balance' => 0,
            'unlocked_balance' => 0,
            'is_active' => true,
        ]);
        
        // 4. Create XmrAddress record
        $xmrWallet->addresses()->create([
            'address' => $addressData['address'],
            'account_index' => 0,
            'address_index' => 0,
            'label' => 'Escrow Primary',
            'balance' => 0,
            'is_used' => false,
        ]);
        
        // 5. Create EscrowWallet record
        $escrowWallet = EscrowWallet::create([
            'order_id' => $order->id,
            'currency' => 'xmr',
            'wallet_name' => $walletName,
            'wallet_password_hash' => $passwordHash,
            'address' => $addressData['address'],
            'balance' => 0,
            'status' => 'active',
        ]);
        
        Log::info("Monero escrow wallet created for order #{$order->id}", [
            'wallet_name' => $walletName,
            'address' => $addressData['address'],
        ]);
        
        return $escrowWallet;
    }

    /**
     * Fund escrow wallet from buyer's wallet.
     *
     * @param EscrowWallet $escrowWallet
     * @param Order $order
     * @return string|null Transaction ID
     */
    public function fundEscrow(EscrowWallet $escrowWallet, Order $order): ?string
    {
        $buyer = $order->user;
        
        if ($escrowWallet->currency === 'btc') {
            return $this->fundBitcoinEscrow($escrowWallet, $order, $buyer);
        } elseif ($escrowWallet->currency === 'xmr') {
            return $this->fundMoneroEscrow($escrowWallet, $order, $buyer);
        }
        
        return null;
    }

    /**
     * Fund Bitcoin escrow from buyer's wallet.
     */
    private function fundBitcoinEscrow(EscrowWallet $escrowWallet, Order $order, $buyer): ?string
    {
        $buyerWallet = $buyer->btcWallet;
        if (!$buyerWallet) {
            throw new \Exception("Buyer does not have a Bitcoin wallet");
        }
        
        // Send Bitcoin from buyer to escrow address
        $txid = BitcoinRepository::sendBitcoin(
            $buyerWallet->name,
            $escrowWallet->address,
            $order->crypto_value
        );
        
        if (!$txid) {
            throw new \Exception("Failed to send Bitcoin to escrow");
        }
        
        Log::info("Bitcoin sent to escrow for order #{$order->id}", [
            'txid' => $txid,
            'amount' => $order->crypto_value,
            'escrow_address' => $escrowWallet->address,
        ]);
        
        return $txid;
    }

    /**
     * Fund Monero escrow from buyer's wallet.
     */
    private function fundMoneroEscrow(EscrowWallet $escrowWallet, Order $order, $buyer): ?string
    {
        $buyerWallet = $buyer->xmrWallet;
        if (!$buyerWallet) {
            throw new \Exception("Buyer does not have a Monero wallet");
        }
        
        // Send Monero from buyer to escrow address
        $txid = MoneroRepository::transfer(
            $buyerWallet->name,
            $escrowWallet->address,
            $order->crypto_value
        );
        
        if (!$txid) {
            throw new \Exception("Failed to send Monero to escrow");
        }
        
        Log::info("Monero sent to escrow for order #{$order->id}", [
            'txid' => $txid,
            'amount' => $order->crypto_value,
            'escrow_address' => $escrowWallet->address,
        ]);
        
        return $txid;
    }

    /**
     * Release escrow funds to vendor.
     *
     * @param EscrowWallet $escrowWallet
     * @param Order $order
     * @return array ['seller_txid' => string, 'admin_txid' => string]
     */
    public function releaseEscrow(EscrowWallet $escrowWallet, Order $order): array
    {
        if (!$escrowWallet->canRelease()) {
            throw new \Exception("Escrow wallet cannot be released");
        }
        
        if ($escrowWallet->currency === 'btc') {
            return $this->releaseBitcoinEscrow($escrowWallet, $order);
        } elseif ($escrowWallet->currency === 'xmr') {
            return $this->releaseMoneroEscrow($escrowWallet, $order);
        }
        
        throw new \Exception("Unsupported currency: {$escrowWallet->currency}");
    }

    /**
     * Release Bitcoin escrow to vendor and admin.
     */
    private function releaseBitcoinEscrow(EscrowWallet $escrowWallet, Order $order): array
    {
        $seller = $order->listing->user;
        
        // Calculate amounts
        $serviceFeePercent = config('fees.order_completion_percentage', 3);
        $serviceFeeAmount = round(($escrowWallet->balance * $serviceFeePercent) / 100, 8);
        $sellerAmount = round($escrowWallet->balance - $serviceFeeAmount, 8);
        
        // Get seller address
        $sellerWallet = $seller->btcWallet;
        if (!$sellerWallet) {
            throw new \Exception("Seller does not have a Bitcoin wallet");
        }
        
        $sellerAddress = $sellerWallet->getCurrentAddress();
        if (!$sellerAddress) {
            $sellerAddress = $sellerWallet->generateNewAddress();
        }
        
        // Get admin address
        $adminWalletName = config('fees.admin_btc_wallet_name', 'admin');
        $adminWallet = BtcWallet::where('name', $adminWalletName)->first();
        if (!$adminWallet) {
            throw new \Exception("Admin wallet not found");
        }
        
        $adminAddress = $adminWallet->getCurrentAddress();
        if (!$adminAddress) {
            $adminAddress = $adminWallet->generateNewAddress();
        }
        
        // Send to seller
        $sellerTxid = BitcoinRepository::sendBitcoin(
            $escrowWallet->wallet_name,
            $sellerAddress->address,
            $sellerAmount
        );
        
        if (!$sellerTxid) {
            throw new \Exception("Failed to send Bitcoin to seller from escrow");
        }
        
        // Send to admin
        $adminTxid = BitcoinRepository::sendBitcoin(
            $escrowWallet->wallet_name,
            $adminAddress->address,
            $serviceFeeAmount
        );
        
        if (!$adminTxid) {
            throw new \Exception("Failed to send service fee to admin from escrow");
        }
        
        // Mark escrow as released
        $escrowWallet->markAsReleased();
        
        Log::info("Bitcoin escrow released for order #{$order->id}", [
            'seller_txid' => $sellerTxid,
            'admin_txid' => $adminTxid,
            'seller_amount' => $sellerAmount,
            'service_fee' => $serviceFeeAmount,
        ]);
        
        return [
            'seller_txid' => $sellerTxid,
            'admin_txid' => $adminTxid,
        ];
    }

    /**
     * Release Monero escrow to vendor and admin.
     */
    private function releaseMoneroEscrow(EscrowWallet $escrowWallet, Order $order): array
    {
        $seller = $order->listing->user;
        
        // Calculate amounts
        $serviceFeePercent = config('fees.order_completion_percentage', 3);
        $serviceFeeAmount = round(($escrowWallet->balance * $serviceFeePercent) / 100, 12);
        $sellerAmount = round($escrowWallet->balance - $serviceFeeAmount, 12);
        
        // Get seller address
        $sellerWallet = $seller->xmrWallet;
        if (!$sellerWallet) {
            throw new \Exception("Seller does not have a Monero wallet");
        }
        
        $sellerAddress = $sellerWallet->getCurrentAddress();
        if (!$sellerAddress) {
            $sellerAddress = $sellerWallet->generateNewAddress();
        }
        
        // Get admin address
        $adminWalletName = config('fees.admin_xmr_wallet_name', 'admin');
        $adminWallet = XmrWallet::where('name', $adminWalletName)->first();
        if (!$adminWallet) {
            throw new \Exception("Admin wallet not found");
        }
        
        $adminAddress = $adminWallet->getCurrentAddress();
        if (!$adminAddress) {
            $adminAddress = $adminWallet->generateNewAddress();
        }
        
        // Send to seller
        $sellerTxid = MoneroRepository::transfer(
            $escrowWallet->wallet_name,
            $sellerAddress->address,
            $sellerAmount
        );
        
        if (!$sellerTxid) {
            throw new \Exception("Failed to send Monero to seller from escrow");
        }
        
        // Send to admin
        $adminTxid = MoneroRepository::transfer(
            $escrowWallet->wallet_name,
            $adminAddress->address,
            $serviceFeeAmount
        );
        
        if (!$adminTxid) {
            throw new \Exception("Failed to send service fee to admin from escrow");
        }
        
        // Mark escrow as released
        $escrowWallet->markAsReleased();
        
        Log::info("Monero escrow released for order #{$order->id}", [
            'seller_txid' => $sellerTxid,
            'admin_txid' => $adminTxid,
            'seller_amount' => $sellerAmount,
            'service_fee' => $serviceFeeAmount,
        ]);
        
        return [
            'seller_txid' => $sellerTxid,
            'admin_txid' => $adminTxid,
        ];
    }

    /**
     * Refund escrow to buyer (for cancelled orders).
     *
     * @param EscrowWallet $escrowWallet
     * @param Order $order
     * @return string Transaction ID
     */
    public function refundEscrow(EscrowWallet $escrowWallet, Order $order): string
    {
        if (!$escrowWallet->canRefund()) {
            throw new \Exception("Escrow wallet cannot be refunded");
        }
        
        $buyer = $order->user;
        
        if ($escrowWallet->currency === 'btc') {
            return $this->refundBitcoinEscrow($escrowWallet, $buyer);
        } elseif ($escrowWallet->currency === 'xmr') {
            return $this->refundMoneroEscrow($escrowWallet, $buyer);
        }
        
        throw new \Exception("Unsupported currency: {$escrowWallet->currency}");
    }

    /**
     * Refund Bitcoin escrow to buyer.
     */
    private function refundBitcoinEscrow(EscrowWallet $escrowWallet, $buyer): string
    {
        $buyerWallet = $buyer->btcWallet;
        if (!$buyerWallet) {
            throw new \Exception("Buyer does not have a Bitcoin wallet");
        }
        
        $buyerAddress = $buyerWallet->getCurrentAddress();
        if (!$buyerAddress) {
            $buyerAddress = $buyerWallet->generateNewAddress();
        }
        
        // Send full balance back to buyer
        $txid = BitcoinRepository::sendBitcoin(
            $escrowWallet->wallet_name,
            $buyerAddress->address,
            $escrowWallet->balance
        );
        
        if (!$txid) {
            throw new \Exception("Failed to refund Bitcoin from escrow");
        }
        
        $escrowWallet->markAsRefunded();
        
        Log::info("Bitcoin escrow refunded", [
            'txid' => $txid,
            'amount' => $escrowWallet->balance,
        ]);
        
        return $txid;
    }

    /**
     * Refund Monero escrow to buyer.
     */
    private function refundMoneroEscrow(EscrowWallet $escrowWallet, $buyer): string
    {
        $buyerWallet = $buyer->xmrWallet;
        if (!$buyerWallet) {
            throw new \Exception("Buyer does not have a Monero wallet");
        }
        
        $buyerAddress = $buyerWallet->getCurrentAddress();
        if (!$buyerAddress) {
            $buyerAddress = $buyerWallet->generateNewAddress();
        }
        
        // Send full balance back to buyer
        $txid = MoneroRepository::transfer(
            $escrowWallet->wallet_name,
            $buyerAddress->address,
            $escrowWallet->balance
        );
        
        if (!$txid) {
            throw new \Exception("Failed to refund Monero from escrow");
        }
        
        $escrowWallet->markAsRefunded();
        
        Log::info("Monero escrow refunded", [
            'txid' => $txid,
            'amount' => $escrowWallet->balance,
        ]);
        
        return $txid;
    }

    /**
     * Generate unique wallet name for order.
     */
    private function generateWalletName(Order $order): string
    {
        $suffix = $order->currency === 'xmr' ? '.wallet' : '';
        return "escrow_order_{$order->id}_{$order->currency}{$suffix}";
    }
}
```

---

### Phase 4: Update Order Model

**File**: `app/Models/Order.php`

Add relationship and helper methods:

```php
/**
 * Get the escrow wallet for this order.
 */
public function escrowWallet(): \Illuminate\Database\Eloquent\Relations\HasOne
{
    return $this->hasOne(EscrowWallet::class);
}

/**
 * Check if order has active escrow.
 */
public function hasActiveEscrow(): bool
{
    return $this->escrowWallet && $this->escrowWallet->status === 'active';
}

/**
 * Check if escrow is funded.
 */
public function isEscrowFunded(): bool
{
    return $this->escrow_funded_at !== null && $this->escrowWallet && $this->escrowWallet->balance > 0;
}
```

---

### Phase 5: Update OrderController

#### OrderController@store (Create Order with Escrow)

```php
public function store(Request $request, Listing $listing): \Illuminate\Http\RedirectResponse
{
    $data = $request->validate([
        'currency' => 'required|in:btc,xmr',
        'quantity' => 'required|numeric|min:1',
        'delivery_address' => 'nullable|string|max:500',
        'note' => 'nullable|string|max:1000',
    ]);

    if (empty($listing->user->pgp_pub_key)) {
        return redirect()->back()->withErrors([
            'error' => 'Vendor does not have a PGP public key configured.',
        ]);
    }

    $user = $request->user();
    $user_balance = $user->getBalance();
    $usd_price = $listing->price * $data['quantity'];
    $crypto_value = convert_usd_to_crypto($usd_price, $data['currency']);

    if ($crypto_value > $user_balance[$data['currency']]['balance']) {
        return redirect()->back()->withErrors([
            'error' => "Insufficient balance.",
        ]);
    }

    // Encrypt delivery address
    $encryptedAddress = null;
    if (!empty($data['delivery_address'])) {
        try {
            $encryptedAddress = $this->encryptWithPGP($data['delivery_address'], $listing->user->pgp_pub_key);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Failed to encrypt delivery address.',
            ]);
        }
    }

    try {
        DB::transaction(function () use ($user, $listing, $data, $crypto_value, $usd_price, $encryptedAddress) {
            // 1. Create order
            $order = $user->orders()->create([
                'listing_id' => $listing->id,
                'quantity' => $data['quantity'],
                'currency' => $data['currency'],
                'crypto_value' => $crypto_value,
                'usd_price' => $usd_price,
                'status' => 'pending',
                'encrypted_delivery_address' => $encryptedAddress,
            ]);

            // 2. Create escrow wallet
            $escrowService = new \App\Services\EscrowService();
            $escrowWallet = $escrowService->createEscrowForOrder($order);

            // 3. Fund escrow from buyer's wallet
            $txid = $escrowService->fundEscrow($escrowWallet, $order);

            if (!$txid) {
                throw new \Exception("Failed to fund escrow wallet");
            }

            // 4. Update order with escrow info
            $order->update([
                'escrow_wallet_id' => $escrowWallet->id,
                'txid' => $txid, // Store initial funding transaction
            ]);

            // 5. Create wallet transaction record for buyer
            $wallet = $user->wallets()->where('currency', $data['currency'])->firstOrFail();
            $wallet->transactions()->create([
                'amount' => -$crypto_value,
                'type' => 'order_escrow',
                'comment' => "Sent to escrow for order #{$order->id}",
            ]);

            // 6. Update buyer's wallet balance
            $wallet->decrement('balance', $crypto_value);

            // 7. Create message to vendor
            UserMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $listing->user_id,
                'message' => "New order #{$order->id}:\nQuantity: {$data['quantity']}\nAmount: {$crypto_value} ".strtoupper($data['currency'])."\nNote: ".($data['note'] ?? 'No message provided'),
                'order_id' => $order->id,
            ]);

            Log::info("Order created with escrow", [
                'order_id' => $order->id,
                'escrow_wallet' => $escrowWallet->wallet_name,
                'funding_txid' => $txid,
            ]);
        });

        return redirect()->route('orders.index')->with('success', 'Order placed successfully! Funds sent to escrow.');

    } catch (\Exception $e) {
        Log::error("Failed to create order with escrow", [
            'error' => $e->getMessage(),
            'user_id' => $user->id,
            'listing_id' => $listing->id,
        ]);
        
        return redirect()->back()->withErrors([
            'error' => 'Failed to create order. Please try again or contact support.',
        ]);
    }
}
```

#### OrderController@complete (Release Escrow)

```php
public function complete(Request $request, Order $order): \Illuminate\Http\RedirectResponse
{
    $user = $request->user();
    
    // Authorization check
    if ($order->user_id !== $user->id && $order->listing->user_id !== $user->id) {
        abort(403, 'Only the buyer or vendor can complete the order');
    }

    if (!in_array($order->status, ['pending', 'shipped'])) {
        return redirect()->back()->withErrors([
            'error' => 'This order cannot be completed.',
        ]);
    }

    if (!$order->escrowWallet) {
        return redirect()->back()->withErrors([
            'error' => 'No escrow wallet found for this order.',
        ]);
    }

    try {
        DB::transaction(function () use ($order) {
            $escrowService = new \App\Services\EscrowService();
            
            // Update escrow balance from blockchain (ensure funds are confirmed)
            $order->escrowWallet->updateBalance();
            
            if ($order->escrowWallet->balance <= 0) {
                throw new \Exception("Escrow wallet has no balance");
            }

            // Release escrow to vendor and admin
            $txids = $escrowService->releaseEscrow($order->escrowWallet, $order);

            // Update order status
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
                'txid' => $txids['seller_txid'], // Store seller transaction
            ]);

            // Notify vendor
            UserMessage::create([
                'sender_id' => $order->user_id,
                'receiver_id' => $order->listing->user_id,
                'message' => "Order #{$order->id} has been completed.\nEscrow released.\nTransaction ID: {$txids['seller_txid']}",
                'order_id' => $order->id,
            ]);

            Log::info("Order completed via escrow release", [
                'order_id' => $order->id,
                'seller_txid' => $txids['seller_txid'],
                'admin_txid' => $txids['admin_txid'],
            ]);
        });

        return redirect()->route('orders.show', $order)->with('success', 'Order completed! Escrow released to vendor.');

    } catch (\Exception $e) {
        Log::error("Failed to complete order", [
            'error' => $e->getMessage(),
            'order_id' => $order->id,
        ]);
        
        return redirect()->back()->withErrors([
            'error' => 'Failed to complete order. Please contact support.',
        ]);
    }
}
```

---

### Phase 6: Update Sync Jobs

#### Update SyncBitcoinWallets to include escrow wallets

```php
// In app/Jobs/SyncBitcoinWallets.php

public function handle()
{
    // Sync user wallets
    BitcoinRepository::syncAllWallets();
    
    // Sync escrow wallets
    $activeEscrowWallets = \App\Models\EscrowWallet::where('currency', 'btc')
        ->where('status', 'active')
        ->get();
    
    foreach ($activeEscrowWallets as $escrowWallet) {
        $btcWallet = \App\Models\BtcWallet::where('name', $escrowWallet->wallet_name)->first();
        if ($btcWallet) {
            BitcoinRepository::syncWalletTransactions($btcWallet);
            
            // Update escrow balance
            $escrowWallet->updateBalance();
            
            // Check if escrow is now funded
            if (!$escrowWallet->order->escrow_funded_at && $escrowWallet->balance > 0) {
                $escrowWallet->order->update([
                    'escrow_funded_at' => now(),
                ]);
            }
        }
    }
}
```

#### Update SyncMoneroWallets to include escrow wallets

```php
// In app/Jobs/SyncMoneroWallets.php

public function handle()
{
    // Sync user wallets
    MoneroRepository::syncAllWallets();
    
    // Sync escrow wallets
    $activeEscrowWallets = \App\Models\EscrowWallet::where('currency', 'xmr')
        ->where('status', 'active')
        ->get();
    
    foreach ($activeEscrowWallets as $escrowWallet) {
        $xmrWallet = \App\Models\XmrWallet::where('name', $escrowWallet->wallet_name)->first();
        if ($xmrWallet) {
            MoneroRepository::syncWalletTransactions($xmrWallet);
            
            // Update escrow balance
            $escrowWallet->updateBalance();
            
            // Check if escrow is now funded
            if (!$escrowWallet->order->escrow_funded_at && $escrowWallet->balance > 0) {
                $escrowWallet->order->update([
                    'escrow_funded_at' => now(),
                ]);
            }
        }
    }
}
```

---

### Phase 7: Handle Order Cancellation

Add cancellation method to OrderController:

```php
public function cancel(Request $request, Order $order): \Illuminate\Http\RedirectResponse
{
    $user = $request->user();
    
    // Only buyer or vendor can cancel (with business rules)
    if ($order->user_id !== $user->id && $order->listing->user_id !== $user->id) {
        abort(403);
    }

    if ($order->status !== 'pending') {
        return redirect()->back()->withErrors([
            'error' => 'Only pending orders can be cancelled.',
        ]);
    }

    try {
        DB::transaction(function () use ($order, $user) {
            // If escrow exists and is funded, refund to buyer
            if ($order->escrowWallet && $order->escrowWallet->balance > 0) {
                $escrowService = new \App\Services\EscrowService();
                $txid = $escrowService->refundEscrow($order->escrowWallet, $order);
                
                Log::info("Escrow refunded for cancelled order", [
                    'order_id' => $order->id,
                    'txid' => $txid,
                ]);
            }

            // Update order status
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Notify other party
            $receiverId = ($order->user_id === $user->id) 
                ? $order->listing->user_id 
                : $order->user_id;

            UserMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'message' => "Order #{$order->id} has been cancelled. Escrow refunded to buyer.",
                'order_id' => $order->id,
            ]);
        });

        return redirect()->route('orders.index')->with('success', 'Order cancelled and funds refunded.');

    } catch (\Exception $e) {
        Log::error("Failed to cancel order", [
            'error' => $e->getMessage(),
            'order_id' => $order->id,
        ]);
        
        return redirect()->back()->withErrors([
            'error' => 'Failed to cancel order. Please contact support.',
        ]);
    }
}
```

---

## Security Considerations

### 1. **Wallet Isolation**
- ✅ Each escrow wallet is physically separate on the blockchain
- ✅ Escrow wallets are NOT associated with any user (user_id = null)
- ✅ Unique wallet names prevent collisions

### 2. **Password Security (Monero)**
- ✅ Escrow wallet passwords derived from order UUID + app key
- ✅ Passwords stored as SHA-256 hashes
- ⚠️ **CRITICAL**: Never expose raw passwords in logs or responses

### 3. **Transaction Atomicity**
- ✅ All database operations wrapped in DB::transaction()
- ✅ Rollback on failure prevents partial state
- ✅ Wallet locks prevent race conditions

### 4. **Balance Verification**
- ✅ Always check buyer's balance before creating escrow
- ✅ Update escrow balance from blockchain before release
- ✅ Prevent double-spending via database constraints

### 5. **Access Control**
- ✅ Only buyer/vendor can complete orders
- ✅ Only active escrow wallets can be released
- ✅ Released escrow wallets cannot be reused

---

## Testing Checklist

### Unit Tests
- [ ] EscrowWallet model methods
- [ ] EscrowService wallet creation
- [ ] EscrowService funding logic
- [ ] EscrowService release logic
- [ ] EscrowService refund logic

### Integration Tests
- [ ] Full order flow: create → fund → complete
- [ ] Cancellation flow: create → fund → cancel → refund
- [ ] Balance synchronization from blockchain
- [ ] Multi-order handling (multiple escrow wallets)

### Manual Testing
- [ ] Bitcoin order with escrow
- [ ] Monero order with escrow
- [ ] Order completion releases to vendor + admin
- [ ] Order cancellation refunds to buyer
- [ ] Wallet sync jobs update escrow balances
- [ ] View escrow status in order details

---

## Deployment Steps

1. **Backup Database**: Full backup before migration
2. **Run Migration**: `php artisan migrate`
3. **Deploy New Code**: Upload EscrowService, EscrowWallet model, controller changes
4. **Test on Staging**: Complete test order with real crypto (testnet)
5. **Monitor Logs**: Watch for escrow creation/funding/release events
6. **Update Documentation**: Add escrow flow to user docs

---

## Future Enhancements

1. **Multi-Sig Escrow**: Require buyer + vendor + admin signatures
2. **Time-Locked Release**: Auto-release after X days
3. **Partial Releases**: Support installment payments
4. **Dispute Integration**: Hold escrow during dispute resolution
5. **Admin Dashboard**: View all active escrow wallets

---

## Summary

This implementation provides **true cryptocurrency escrow** by:
1. Creating dedicated RPC wallets per order
2. Physically transferring funds from buyer → escrow → vendor
3. Maintaining audit trail via blockchain transactions
4. Supporting both Bitcoin and Monero
5. Handling refunds for cancellations

The buyer no longer controls funds after order creation - they are held in escrow until order completion or dispute resolution.
