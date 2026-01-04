<?php

namespace App\Services;

use App\Models\Order;
use App\Models\EscrowWallet;
use App\Models\BtcWallet;
use App\Models\XmrWallet;
use App\Repositories\BitcoinRepository;
use App\Repositories\MoneroRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

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

        // Create transaction record for internal transfer (so updateBalance works)
        $btcWallet = BtcWallet::where('name', $escrowWallet->wallet_name)->first();
        if ($btcWallet) {
            $btcWallet->transactions()->create([
                'txid' => $txid,
                'amount' => $order->crypto_value,
                'type' => 'deposit',
                'status' => 'confirmed',
                'address' => $escrowWallet->address,
                'confirmations' => 1,
            ]);

            // Update balance immediately for internal transfer
            $btcWallet->updateBalance();
            $escrowWallet->update(['balance' => $btcWallet->balance]);
        }

        // Mark order as funded immediately (internal transfers are instant)
        $order->update(['escrow_funded_at' => now()]);

        Log::info("Escrow wallet funded for order #{$order->id}", [
            'balance' => $escrowWallet->balance,
            'funded_at' => $order->fresh()->escrow_funded_at,
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

        // Create transaction record for internal transfer (so updateBalance works)
        $xmrWallet = XmrWallet::where('name', $escrowWallet->wallet_name)->first();
        if ($xmrWallet) {
            $xmrWallet->transactions()->create([
                'txid' => $txid,
                'amount' => $order->crypto_value,
                'type' => 'deposit',
                'status' => 'unlocked',
                'address' => $escrowWallet->address,
                'height' => 0,
            ]);

            // Update balance immediately for internal transfer
            $xmrWallet->updateBalance();
            $escrowWallet->update(['balance' => $xmrWallet->unlocked_balance]);
        }

        // Mark order as funded immediately (internal transfers are instant)
        $order->update(['escrow_funded_at' => now()]);

        Log::info("Escrow wallet funded for order #{$order->id}", [
            'balance' => $escrowWallet->balance,
            'funded_at' => $order->fresh()->escrow_funded_at,
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
