<?php

namespace App\Services;

use App\Models\Order;
use App\Models\EscrowWallet;
use App\Models\BtcWallet;
use App\Models\XmrWallet;
use App\Models\XmrTransaction;
use App\Models\User;
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
            'wallet_password_encrypted' => null,
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
     * Create Monero escrow wallet — per-order wallet file on RPC.
     */
    private function createMoneroEscrow(Order $order, string $walletName): EscrowWallet
    {
        $repository = new MoneroRepository();

        if (!$repository->isRpcAvailable()) {
            throw new \Exception("Monero RPC service is not available");
        }

        // Deterministic password for the escrow wallet file
        $rawPassword = MoneroRepository::generateWalletPassword('escrow_order_' . $order->id);
        $encryptedPassword = Crypt::encryptString($rawPassword);

        // Create a dedicated wallet file on RPC (e.g. "escrow_order_42_xmr")
        $walletData = $repository->createWalletFile($walletName, $rawPassword);

        // Create XmrWallet record (no user_id — this is an escrow wallet)
        $xmrWallet = XmrWallet::create([
            'user_id' => null,
            'name' => $walletName,
            'primary_address' => $walletData['address'],
            'view_key' => $walletData['view_key'],
            'spend_key_encrypted' => $walletData['spend_key'] ? Crypt::encryptString($walletData['spend_key']) : null,
            'seed_encrypted' => $walletData['seed'] ? Crypt::encryptString($walletData['seed']) : null,
            'password_encrypted' => $encryptedPassword,
            'height' => $walletData['height'],
            'balance' => 0,
            'unlocked_balance' => 0,
            'is_active' => true,
        ]);

        // Create initial address record
        $xmrWallet->addresses()->create([
            'address' => $walletData['address'],
            'account_index' => 0,
            'address_index' => 0,
            'label' => "Escrow Order #{$order->id}",
            'balance' => 0,
            'total_received' => 0,
            'tx_count' => 0,
            'is_used' => false,
        ]);

        // Create EscrowWallet record
        $escrowWallet = EscrowWallet::create([
            'order_id' => $order->id,
            'currency' => 'xmr',
            'wallet_name' => $walletName,
            'wallet_password_encrypted' => $encryptedPassword,
            'address' => $walletData['address'],
            'balance' => 0,
            'status' => 'active',
        ]);

        Log::info("Monero escrow wallet created for order #{$order->id}", [
            'wallet_name' => $walletName,
            'address' => $walletData['address'],
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
            Log::info("Creating XMR wallet for buyer #{$buyer->id} during escrow funding");
            $buyerWallet = MoneroRepository::getOrCreateWalletForUser($buyer);
        }

        // Pre-flight: verify actual on-chain balance before attempting transfer.
        $rpcBalance = $buyerWallet->getRpcBalance();
        if ($rpcBalance['unlocked_balance'] < $order->crypto_value) {
            throw new \Exception(
                "Insufficient Monero balance for escrow. " .
                "Required: {$order->crypto_value} XMR, " .
                "Available (on-chain): {$rpcBalance['unlocked_balance']} XMR"
            );
        }

        // Send from buyer's per-user wallet to the escrow wallet address
        $repository = new MoneroRepository();
        $result = $repository->transfer($buyerWallet, $escrowWallet->address, $order->crypto_value);

        $txid = $result['tx_hash'];

        Log::info("Monero sent to escrow for order #{$order->id}", [
            'txid' => $txid,
            'amount' => $order->crypto_value,
            'fee' => $result['fee'],
            'escrow_address' => $escrowWallet->address,
        ]);

        // Set EscrowWallet balance directly from the known transfer amount
        $escrowWallet->update(['balance' => $order->crypto_value]);

        // Mark order as funded immediately (internal transfer)
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

        // For XMR escrow, always refresh from actual on-chain data before release.
        // The DB balance may be stale or differ from actual wallet balance.
        if ($escrowWallet->currency === 'xmr') {
            $escrowXmrWallet = XmrWallet::where('name', $escrowWallet->wallet_name)->first();
            if ($escrowXmrWallet) {
                $rpcBalance = $escrowXmrWallet->getRpcBalance();

                // Always update DB with authoritative RPC values
                $escrowWallet->update(['balance' => $rpcBalance['unlocked_balance']]);
                $escrowXmrWallet->update([
                    'balance' => $rpcBalance['balance'],
                    'unlocked_balance' => $rpcBalance['unlocked_balance'],
                ]);
                $escrowWallet->refresh();

                Log::info("Refreshed XMR escrow balance from RPC before release", [
                    'escrow_wallet_id' => $escrowWallet->id,
                    'order_id' => $order->id,
                    'rpc_balance' => $rpcBalance['balance'],
                    'rpc_unlocked' => $rpcBalance['unlocked_balance'],
                    'db_balance' => $escrowWallet->balance,
                ]);

                // Funds exist but aren't spendable yet (waiting for confirmations)
                if ($rpcBalance['balance'] > 0 && $rpcBalance['unlocked_balance'] <= 0) {
                    throw new \Exception(
                        "Escrow funds are still being confirmed on the Monero network. This typically takes 10-15 minutes. " .
                        "Please check back soon and we'll release your funds as soon as they're ready!"
                    );
                }
            }
        }

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
     * Release Monero escrow to vendor.
     * Uses sweep_all to send the entire escrow balance (minus network fee) to the seller.
     * Admin fees are tracked in DB on the order and collected on vendor withdrawal.
     * This avoids multi-destination transfer math that fails when
     * seller + admin + networkFee > walletBalance.
     */
    private function releaseMoneroEscrow(EscrowWallet $escrowWallet, Order $order): array
    {
        $seller = $order->listing->user;

        // Get or create seller XMR wallet (vendor may not have one yet)
        $sellerWallet = $seller->xmrWallet;
        if (!$sellerWallet) {
            Log::info("Creating XMR wallet for seller #{$seller->id} during escrow release");
            $sellerWallet = MoneroRepository::getOrCreateWalletForUser($seller);
        }

        $sellerAddress = $sellerWallet->getCurrentAddress();
        if (!$sellerAddress) {
            $sellerAddress = $sellerWallet->generateNewAddress();
        }

        // Look up escrow XMR wallet by wallet_name (unique per-order wallet file)
        $escrowXmrWallet = XmrWallet::where('name', $escrowWallet->wallet_name)->first();
        if (!$escrowXmrWallet) {
            throw new \Exception("Escrow XMR wallet record not found");
        }

        // Calculate admin fee from the order's crypto_value (authoritative source)
        $serviceFeePercent = config('fees.order_completion_percentage', 3);
        $adminFeeAmount = round(($order->crypto_value * $serviceFeePercent) / 100, 12);

        // Single wallet session: open -> refresh -> sweep_all to seller -> close
        $repository = new MoneroRepository();
        $result = $repository->withWalletModel($escrowXmrWallet, function (MoneroRepository $repo) use (
            $escrowWallet, $escrowXmrWallet, $order, $seller, $sellerAddress
        ) {
            // Get actual live balance from the already-open, already-refreshed wallet
            $balance = $repo->getOpenWalletBalance();
            $actualUnlocked = $balance['unlocked_balance'];

            Log::info("Releasing Monero escrow for order #{$order->id}", [
                'rpc_balance' => $balance['balance'],
                'rpc_unlocked' => $actualUnlocked,
                'db_balance' => $escrowWallet->balance,
                'seller_id' => $seller->id,
            ]);

            if ($actualUnlocked <= 0) {
                throw new \Exception(
                    "Escrow wallet has no unlocked funds. " .
                    "Total balance: {$balance['balance']} XMR, Unlocked: {$actualUnlocked} XMR. " .
                    "Please wait for confirmations and try again."
                );
            }

            // sweep_all: sends entire wallet balance minus network fee to seller
            // This always works — no multi-destination math, no fee headroom issues
            $sweepResult = $repo->rpcCall('sweep_all', [
                'address' => $sellerAddress->address,
                'account_index' => 0,
                'priority' => 1,
                'get_tx_key' => true,
            ]);

            if (!$sweepResult || !isset($sweepResult['tx_hash_list'][0])) {
                throw new \Exception("sweep_all failed from escrow wallet '{$escrowXmrWallet->name}'");
            }

            $txHash = $sweepResult['tx_hash_list'][0];
            $amountSent = ($sweepResult['amount_list'][0] ?? 0) / 1e12;
            $fee = ($sweepResult['fee_list'][0] ?? 0) / 1e12;

            Log::info("XMR escrow sweep_all sent to seller", [
                'wallet' => $escrowXmrWallet->name,
                'order_id' => $order->id,
                'seller_id' => $seller->id,
                'amount_sent' => $amountSent,
                'network_fee' => $fee,
                'tx_hash' => $txHash,
                'seller_address' => $sellerAddress->address,
            ]);

            return [
                'tx_hash' => $txHash,
                'fee' => $fee,
                'amount_sent' => $amountSent,
            ];
        });

        $txHash = $result['tx_hash'];

        // Mark escrow as released
        $escrowWallet->markAsReleased();

        // Track admin fee on the order — collected on vendor withdrawal
        $order->update([
            'admin_fee_crypto' => $adminFeeAmount,
            'admin_fee_currency' => 'xmr',
        ]);

        Log::info("Monero escrow released for order #{$order->id}", [
            'tx_hash' => $txHash,
            'amount_sent_to_seller' => $result['amount_sent'],
            'network_fee' => $result['fee'],
            'admin_fee_tracked' => $adminFeeAmount,
            'seller_address' => $sellerAddress->address,
        ]);

        return [
            'seller_txid' => $txHash,
            'admin_txid' => null, // Admin fee collected on vendor withdrawal, not on-chain now
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
        // For XMR escrow, always refresh from actual on-chain data before refund.
        if ($escrowWallet->currency === 'xmr') {
            $escrowXmrWallet = XmrWallet::where('name', $escrowWallet->wallet_name)->first();
            if ($escrowXmrWallet) {
                $rpcBalance = $escrowXmrWallet->getRpcBalance();

                // Always update DB with authoritative RPC values
                $escrowWallet->update(['balance' => $rpcBalance['unlocked_balance']]);
                $escrowXmrWallet->update([
                    'balance' => $rpcBalance['balance'],
                    'unlocked_balance' => $rpcBalance['unlocked_balance'],
                ]);
                $escrowWallet->refresh();

                Log::info("Refreshed XMR escrow balance from RPC before refund", [
                    'escrow_wallet_id' => $escrowWallet->id,
                    'order_id' => $order->id,
                    'rpc_balance' => $rpcBalance['balance'],
                    'rpc_unlocked' => $rpcBalance['unlocked_balance'],
                    'db_balance' => $escrowWallet->balance,
                ]);

                // Funds exist but aren't spendable yet (waiting for confirmations)
                if ($rpcBalance['balance'] > 0 && $rpcBalance['unlocked_balance'] <= 0) {
                    throw new \Exception(
                        "Escrow funds are not yet unlocked. " .
                        "Monero requires confirmations before funds can be spent. " .
                        "Balance: {$rpcBalance['balance']} XMR, Unlocked: {$rpcBalance['unlocked_balance']} XMR. " .
                        "Please try again in a few minutes."
                    );
                }
            }
        }

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
     * Refund Monero escrow to buyer using sweep_all.
     * The entire escrow wallet balance (minus network fee) goes to the buyer.
     */
    private function refundMoneroEscrow(EscrowWallet $escrowWallet, $buyer): string
    {
        $buyerWallet = $buyer->xmrWallet;
        if (!$buyerWallet) {
            Log::info("Creating XMR wallet for buyer #{$buyer->id} during escrow refund");
            $buyerWallet = MoneroRepository::getOrCreateWalletForUser($buyer);
        }

        $buyerAddress = $buyerWallet->getCurrentAddress();
        if (!$buyerAddress) {
            $buyerAddress = $buyerWallet->generateNewAddress();
        }

        // Look up escrow XMR wallet by wallet_name (unique per-order wallet file)
        $escrowXmrWallet = XmrWallet::where('name', $escrowWallet->wallet_name)->first();
        if (!$escrowXmrWallet) {
            throw new \Exception("Escrow XMR wallet record not found");
        }

        // Single wallet session: open -> refresh -> check balance -> sweep_all -> close
        $repository = new MoneroRepository();
        $result = $repository->withWalletModel($escrowXmrWallet, function (MoneroRepository $repo) use (
            $escrowXmrWallet, $buyerAddress, $buyer
        ) {
            // Check actual unlocked balance before sweep
            $balance = $repo->getOpenWalletBalance();

            if ($balance['unlocked_balance'] <= 0) {
                throw new \Exception(
                    "Escrow wallet has no unlocked funds for refund. " .
                    "Total balance: {$balance['balance']} XMR, Unlocked: {$balance['unlocked_balance']} XMR. " .
                    "Please wait for confirmations and try again."
                );
            }

            // sweep_all: send entire wallet balance minus fee to buyer
            $sweepResult = $repo->rpcCall('sweep_all', [
                'address' => $buyerAddress->address,
                'account_index' => 0,
                'priority' => 1,
                'get_tx_key' => true,
            ]);

            if (!$sweepResult || !isset($sweepResult['tx_hash_list'][0])) {
                throw new \Exception("sweep_all failed from escrow wallet '{$escrowXmrWallet->name}'");
            }

            $txHash = $sweepResult['tx_hash_list'][0];

            Log::info("XMR escrow sweep_all sent for refund", [
                'wallet' => $escrowXmrWallet->name,
                'to' => $buyerAddress->address,
                'buyer_id' => $buyer->id,
                'amount' => ($sweepResult['amount_list'][0] ?? 0) / 1e12,
                'fee' => ($sweepResult['fee_list'][0] ?? 0) / 1e12,
                'tx_hash' => $txHash,
            ]);

            return [
                'tx_hash' => $txHash,
                'amount' => ($sweepResult['amount_list'][0] ?? 0) / 1e12,
                'fee' => ($sweepResult['fee_list'][0] ?? 0) / 1e12,
            ];
        });

        $txid = $result['tx_hash'];

        // Mark escrow as refunded
        $escrowWallet->markAsRefunded();

        Log::info("Monero escrow refunded", [
            'txid' => $txid,
            'amount' => $result['amount'],
            'fee' => $result['fee'],
            'buyer_id' => $buyer->id,
            'buyer_address' => $buyerAddress->address,
            'order_id' => $escrowWallet->order_id,
        ]);

        return $txid;
    }

    /**
     * Generate unique wallet name for order.
     */
    private function generateWalletName(Order $order): string
    {
        $suffix = $order->currency === 'xmr' ? '' : '';
        return "escrow_order_{$order->id}_{$order->currency}";
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
     * Sends the full order amount to the vendor. Admin fee is tracked in DB
     * and collected on vendor withdrawal — same pattern as escrow release.
     */
    private function processDirectMoneroPayment(Order $order, User $vendor): array
    {
        $buyer = $order->user;

        // Calculate admin fee (tracked in DB, not sent on-chain now)
        $adminFeePercentage = config('fees.order_completion_percentage', 3);
        $adminFee = round($order->crypto_value * ($adminFeePercentage / 100), 12);

        // Get or create buyer XMR wallet
        $buyerXmrWallet = $buyer->xmrWallet;
        if (!$buyerXmrWallet) {
            Log::info("Creating XMR wallet for buyer #{$buyer->id} during direct payment");
            $buyerXmrWallet = MoneroRepository::getOrCreateWalletForUser($buyer);
        }

        // Get or create vendor XMR wallet
        $vendorXmrWallet = $vendor->xmrWallet;
        if (!$vendorXmrWallet) {
            Log::info("Creating XMR wallet for vendor #{$vendor->id} during direct payment");
            $vendorXmrWallet = MoneroRepository::getOrCreateWalletForUser($vendor);
        }

        $vendorAddress = $vendorXmrWallet->getCurrentAddress();
        if (!$vendorAddress) {
            $vendorAddress = $vendorXmrWallet->generateNewAddress();
        }

        // Single transfer: full order amount to vendor (buyer has excess balance for fee)
        $repository = new MoneroRepository();
        $result = $repository->transfer($buyerXmrWallet, $vendorAddress->address, $order->crypto_value);

        $txHash = $result['tx_hash'];

        // Track admin fee on the order — collected on vendor withdrawal
        $order->update([
            'admin_fee_crypto' => $adminFee,
            'admin_fee_currency' => 'xmr',
        ]);

        Log::info("Direct Monero payment processed", [
            'order_id' => $order->id,
            'tx_hash' => $txHash,
            'amount_sent' => $order->crypto_value,
            'admin_fee_tracked' => $adminFee,
            'tx_fee' => $result['fee'],
        ]);

        return [
            'vendor_txid' => $txHash,
            'admin_txid' => null, // Admin fee collected on vendor withdrawal
        ];
    }
}
