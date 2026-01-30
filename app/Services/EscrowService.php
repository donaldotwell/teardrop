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
     * Create Monero escrow wallet using subaddress from master wallet.
     * NEW ARCHITECTURE: Uses subaddress from master wallet (account 0) with escrow label.
     */
    private function createMoneroEscrow(Order $order, string $walletName): EscrowWallet
    {
        $masterWalletName = config('monero.master_wallet_name', 'teardrop_master');

        // Create escrow subaddress in account 0 (same as users, differentiated by label)
        // RPC automatically assigns next available address_index
        $subaddressData = MoneroRepository::createSubaddress(
            $masterWalletName,
            0, // account_index = 0 (master wallet only has account 0)
            "Escrow Order #{$order->id}"
        );

        if (!$subaddressData || !isset($subaddressData['address'])) {
            throw new \Exception("Failed to create Monero escrow subaddress for order #{$order->id}");
        }

        $address = $subaddressData['address'];
        $createdAddressIndex = $subaddressData['address_index'];

        // Create XmrWallet record for escrow (points to master wallet)
        $xmrWallet = \App\Models\XmrWallet::create([
            'user_id' => null, // Escrow wallets don't belong to users
            'name' => $masterWalletName,
            'primary_address' => $address,
            'view_key' => null,
            'spend_key_encrypted' => null,
            'seed_encrypted' => null,
            'password_hash' => null,
            'height' => 0,
            'balance' => 0,
            'unlocked_balance' => 0,
            'is_active' => true,
        ]);

        // Create XmrAddress record (account 0, escrow identified by label)
        $xmrWallet->addresses()->create([
            'address' => $address,
            'account_index' => 0, // Same account as users
            'address_index' => $createdAddressIndex,
            'label' => "Escrow Order #{$order->id}",
            'balance' => 0,
            'is_used' => false,
        ]);

        // Create EscrowWallet record
        $escrowWallet = EscrowWallet::create([
            'order_id' => $order->id,
            'currency' => 'xmr',
            'wallet_name' => $walletName, // Keep original wallet name for reference
            'wallet_password_hash' => null, // No password needed with subaddress
            'address' => $address,
            'balance' => 0,
            'status' => 'active',
        ]);

        Log::info("Monero escrow subaddress created for order #{$order->id}", [
            'master_wallet' => $masterWalletName,
            'account_index' => 0,
            'address_index' => $createdAddressIndex,
            'address' => $address,
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

        // INTELLIGENT FEE CALCULATION:
        // Step 1: Estimate network fees for BOTH transactions
        $serviceFeePercent = config('fees.order_completion_percentage', 3);
        $estimatedServiceFee = round(($escrowWallet->balance * $serviceFeePercent) / 100, 8);
        $estimatedSellerAmount = round($escrowWallet->balance - $estimatedServiceFee, 8);

        // Estimate network fees using fee tier system
        $sellerNetworkFee = estimate_btc_transaction_fee($estimatedSellerAmount);
        $adminNetworkFee = estimate_btc_transaction_fee($estimatedServiceFee);

        Log::info("Escrow release fee calculation for order #{$order->id}", [
            'escrow_balance' => $escrowWallet->balance,
            'estimated_seller_amount' => $estimatedSellerAmount,
            'estimated_service_fee' => $estimatedServiceFee,
            'seller_network_fee' => $sellerNetworkFee,
            'admin_network_fee' => $adminNetworkFee,
        ]);

        // Step 2: Calculate net amount after deducting BOTH network fees
        $totalNetworkFees = $sellerNetworkFee + $adminNetworkFee;
        $netAmountAfterFees = round($escrowWallet->balance - $totalNetworkFees, 8);

        if ($netAmountAfterFees <= 0) {
            throw new \Exception("Escrow balance insufficient to cover network fees");
        }

        // Step 3: Split net amount - seller gets 97%, admin gets 3%
        $serviceFeeAmount = round(($netAmountAfterFees * $serviceFeePercent) / 100, 8);
        $sellerAmount = round($netAmountAfterFees - $serviceFeeAmount, 8);

        Log::info("Final amounts after fee accounting", [
            'net_after_fees' => $netAmountAfterFees,
            'seller_amount' => $sellerAmount,
            'service_fee_amount' => $serviceFeeAmount,
            'total_network_fees' => $totalNetworkFees,
        ]);

        // Step 4: Send to seller first
        $sellerTxid = BitcoinRepository::sendBitcoin(
            $escrowWallet->wallet_name,
            $sellerAddress->address,
            $sellerAmount
        );

        if (!$sellerTxid) {
            throw new \Exception("Failed to send Bitcoin to seller from escrow");
        }

        Log::info("Seller payment sent", [
            'txid' => $sellerTxid,
            'amount' => $sellerAmount,
        ]);

        // Step 5: Send remaining balance to admin (whatever is left after seller transaction)
        // This is more reliable than using the calculated amount because actual network fees may vary
        // Get current escrow wallet balance from Bitcoin node to see what's actually left
        $repository = new BitcoinRepository();
        $remainingBalance = $repository->getWalletBalance($escrowWallet->wallet_name);

        if ($remainingBalance === null) {
            throw new \Exception("Failed to get escrow wallet balance after seller payment");
        }

        if ($remainingBalance < 0.00001) {
            throw new \Exception("Insufficient balance remaining in escrow for admin fee after seller payment");
        }

        Log::info("Remaining balance after seller payment", [
            'remaining' => $remainingBalance,
            'will_send_to_admin' => $remainingBalance,
        ]);

        // Send ALL remaining balance to admin with fee deducted from amount
        // This ensures we can send the entire remaining balance without "insufficient funds" errors
        // Admin receives slightly less due to network fee, but all escrow funds are cleared
        $adminTxid = BitcoinRepository::sendBitcoin(
            $escrowWallet->wallet_name,
            $adminAddress->address,
            $remainingBalance,
            true  // subtractFeeFromAmount = true (admin pays the network fee)
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
            'admin_amount' => $remainingBalance,
            'total_network_fees' => $totalNetworkFees,
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

        // estimate amount less the fees
        $escrowBalance = $escrowWallet->balance;
        $feeRate = BitcoinRepository::getFeeRateForAmount($escrowBalance);
        $estimatedFee = BitcoinRepository::estimateTransactionFee(1, $feeRate); // 1 output
        $escrowBalance -= $estimatedFee;

        // Send full balance back to buyer
        $txid = BitcoinRepository::sendBitcoin(
            $escrowWallet->wallet_name,
            $buyerAddress->address,
            $escrowBalance
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

    /**
     * Process direct payment to vendor (early finalization).
     *
     * @param \App\Models\Order $order
     * @param \App\Models\User $vendor
     * @return array ['vendor_txid' => string, 'admin_txid' => string]
     * @throws \Exception
     */
    public function processDirectPayment(Order $order, User $vendor): array
    {
        if ($order->currency === 'btc') {
            return $this->processDirectBitcoinPayment($order, $vendor);
        } elseif ($order->currency === 'xmr') {
            return $this->processDirectMoneroPayment($order, $vendor);
        }

        throw new \Exception("Unsupported currency: {$order->currency}");
    }

    /**
     * Process direct Bitcoin payment.
     */
    private function processDirectBitcoinPayment(Order $order, User $vendor): array
    {
        $buyer = $order->user;
        $repository = new BitcoinRepository();

        // Calculate amounts
        $adminFeePercentage = config('fees.order_completion_percentage', 3);
        $adminFee = $order->crypto_value * ($adminFeePercentage / 100);
        $vendorAmount = $order->crypto_value - $adminFee;

        // Get wallets
        $buyerWallet = $buyer->btcWallet;
        $vendorWallet = $vendor->btcWallet;

        if (!$buyerWallet || !$vendorWallet) {
            throw new \Exception("Buyer or vendor does not have a Bitcoin wallet");
        }

        // Lock buyer wallet
        $buyerWallet = BtcWallet::where('id', $buyerWallet->id)->lockForUpdate()->first();

        // Verify balance
        if ($buyerWallet->balance < $order->crypto_value) {
            throw new \Exception("Insufficient buyer balance");
        }

        // Get addresses
        $vendorAddress = $vendorWallet->getCurrentAddress();
        if (!$vendorAddress) {
            $vendorAddress = $vendorWallet->generateNewAddress();
        }

        // Get admin wallet
        $adminWalletName = config('fees.admin_btc_wallet_name', 'admin');
        $adminBtcWallet = BtcWallet::where('name', $adminWalletName)->first();
        if (!$adminBtcWallet) {
            throw new \Exception("Admin Bitcoin wallet not found");
        }

        $adminAddress = $adminBtcWallet->getCurrentAddress();
        if (!$adminAddress) {
            $adminAddress = $adminBtcWallet->generateNewAddress();
        }

        // Send to vendor
        $vendorTxid = $repository->sendToAddress(
            $buyerWallet->name,
            $vendorAddress->address,
            $vendorAmount
        );

        if (!$vendorTxid) {
            throw new \Exception("Failed to send Bitcoin to vendor");
        }

        // Send admin fee
        $adminTxid = $repository->sendToAddress(
            $buyerWallet->name,
            $adminAddress->address,
            $adminFee
        );

        if (!$adminTxid) {
            throw new \Exception("Failed to send admin fee");
        }

        // Deduct from buyer wallet
        $buyerWallet->decrement('balance', $order->crypto_value);

        // Create transaction records
        $buyerWallet->transactions()->create([
            'amount' => -$order->crypto_value,
            'type' => 'direct_order',
            'txid' => $vendorTxid,
            'comment' => "Direct payment for order #{$order->id}",
        ]);

        Log::info("Direct Bitcoin payment processed", [
            'order_id' => $order->id,
            'vendor_txid' => $vendorTxid,
            'admin_txid' => $adminTxid,
            'vendor_amount' => $vendorAmount,
            'admin_fee' => $adminFee,
        ]);

        return [
            'vendor_txid' => $vendorTxid,
            'admin_txid' => $adminTxid,
        ];
    }

    /**
     * Process direct Monero payment.
     */
    private function processDirectMoneroPayment(Order $order, User $vendor): array
    {
        $buyer = $order->user;
        $repository = new MoneroRepository();

        // Calculate amounts
        $adminFeePercentage = config('fees.order_completion_percentage', 3);
        $adminFee = $order->crypto_value * ($adminFeePercentage / 100);
        $vendorAmount = $order->crypto_value - $adminFee;

        // Get buyer wallet
        $buyerWallet = $buyer->wallets()->where('currency', 'xmr')->lockForUpdate()->first();
        if (!$buyerWallet) {
            throw new \Exception("Buyer does not have a Monero wallet");
        }

        // Verify balance
        if ($buyerWallet->balance < $order->crypto_value) {
            throw new \Exception("Insufficient buyer balance");
        }

        // Get vendor XMR wallet
        $vendorXmrWallet = $vendor->xmrWallet;
        if (!$vendorXmrWallet) {
            throw new \Exception("Vendor does not have a Monero wallet");
        }

        $vendorAddress = $vendorXmrWallet->getCurrentAddress();
        if (!$vendorAddress) {
            $vendorAddress = $vendorXmrWallet->generateNewAddress();
        }

        // Get admin wallet (for future implementation)
        // For now, use placeholder - admin XMR wallet needs to be set up
        $adminAddress = config('monero.admin_address');
        if (!$adminAddress) {
            throw new \Exception("Admin Monero address not configured");
        }

        // Send to vendor
        $buyerXmrWallet = $buyer->xmrWallet;
        $vendorTxid = $repository->transfer(
            $buyerXmrWallet->wallet_name,
            $vendorAddress->address,
            $vendorAmount
        );

        if (!$vendorTxid) {
            throw new \Exception("Failed to send Monero to vendor");
        }

        // Send admin fee
        $adminTxid = $repository->transfer(
            $buyerXmrWallet->wallet_name,
            $adminAddress,
            $adminFee
        );

        if (!$adminTxid) {
            throw new \Exception("Failed to send admin fee");
        }

        // Deduct from buyer wallet
        $buyerWallet->decrement('balance', $order->crypto_value);

        // Create transaction records
        $buyerWallet->transactions()->create([
            'amount' => -$order->crypto_value,
            'type' => 'direct_order',
            'txid' => $vendorTxid,
            'comment' => "Direct payment for order #{$order->id}",
        ]);

        Log::info("Direct Monero payment processed", [
            'order_id' => $order->id,
            'vendor_txid' => $vendorTxid,
            'admin_txid' => $adminTxid,
            'vendor_amount' => $vendorAmount,
            'admin_fee' => $adminFee,
        ]);

        return [
            'vendor_txid' => $vendorTxid,
            'admin_txid' => $adminTxid,
        ];
    }
}
