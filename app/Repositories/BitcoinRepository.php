<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\BtcWallet;
use App\Models\BtcAddress;
use App\Models\BtcTransaction;
use Denpa\Bitcoin\Client as BitcoinClient;
use Illuminate\Support\Facades\Log;

class BitcoinRepository
{
    private BitcoinClient $client;

    public function __construct()
    {
        $this->client = new BitcoinClient([
            'scheme' => config('bitcoinrpc.scheme', 'http'),
            'host' => config('bitcoinrpc.host', 'localhost'),
            'port' => config('bitcoinrpc.port', 8332),
            'user' => config('bitcoinrpc.user'),
            'password' => config('bitcoinrpc.password'),
        ]);
    }

    /**
     * Create or get Bitcoin wallet for user.
     */
    public static function getOrCreateWalletForUser(User $user): BtcWallet
    {
        $walletName = $user->username_pri;

        return BtcWallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $walletName,
                'xpub' => null, // Will be set when wallet is created
                'address_index' => 0,
                'is_active' => true
            ]
        );
    }

    /**
     * Generate new receiving address for wallet.
     */
    public static function generateNewAddress(BtcWallet $wallet): BtcAddress
    {
        // Get current address if unused
        $currentAddress = $wallet->getCurrentAddress();
        if ($currentAddress) {
            return $currentAddress;
        }

        // Generate new address
        return $wallet->generateNewAddress();
    }

    /**
     * Check for new transactions on all active wallets.
     */
    public static function syncAllWallets(): void
    {
        Log::debug("=== Starting Bitcoin wallet sync ===");

        $activeWallets = BtcWallet::where('is_active', true)->with('addresses')->get();

        Log::debug("Found {$activeWallets->count()} active wallets to sync");

        foreach ($activeWallets as $wallet) {
            Log::debug("Syncing wallet ID: {$wallet->id}, Name: {$wallet->name}, User ID: {$wallet->user_id}");
            static::syncWalletTransactions($wallet);
        }

        Log::debug("=== Bitcoin wallet sync completed ===");
    }

    /**
     * Sync transactions for specific wallet.
     */
    public static function syncWalletTransactions(BtcWallet $wallet): void
    {
        try {
            Log::debug("  Wallet {$wallet->id}: Starting sync");

            $repository = new static();

            // Get ALL transactions for this wallet from Bitcoin node (once per wallet, not per address)
            $response = $repository->client->wallet($wallet->name)->listTransactions("*", 1000);

            // Handle Bitcoin RPC response object
            $transactions = $response;
            if (is_object($response)) {
                if (method_exists($response, 'result')) {
                    $transactions = $response->result();
                    Log::debug("  Extracted result from RPC response");
                } elseif (method_exists($response, 'toArray')) {
                    $transactions = $response->toArray();
                    Log::debug("  Converted response object to array using toArray()");
                } elseif ($response instanceof \Traversable) {
                    $transactions = iterator_to_array($response);
                    Log::debug("  Converted traversable object to array");
                } else {
                    Log::warning("  Unknown response object type: " . get_class($response));
                    $transactions = [];
                }
            }

            if (!is_array($transactions)) {
                Log::warning("  Transactions is not an array after conversion, type: " . gettype($transactions));
                $transactions = [];
            }

            $txCount = count($transactions);
            Log::debug("  Found {$txCount} transaction(s) from Bitcoin node");

            // Process each transaction
            foreach ($transactions as $tx) {
                Log::debug("    Processing txid: {$tx['txid']}, category: {$tx['category']}, amount: {$tx['amount']}, confirmations: " . ($tx['confirmations'] ?? 0));
                $repository->processWalletTransaction($wallet, $tx);
            }

            $oldBalance = $wallet->balance;
            $wallet->updateBalance();
            $newBalance = $wallet->fresh()->balance;

            Log::debug("  Wallet {$wallet->id}: Balance updated from {$oldBalance} to {$newBalance} BTC");

        } catch (\Exception $e) {
            Log::error("Failed to sync wallet {$wallet->id}: " . $e->getMessage());
        }
    }

    /**
     * Process individual transaction for a wallet.
     */
    private function processWalletTransaction(BtcWallet $wallet, array $txData): void
    {
        Log::debug("      === Processing Transaction ===");
        Log::debug("      TXID: {$txData['txid']}");
        Log::debug("      Category: {$txData['category']}");
        Log::debug("      Amount: {$txData['amount']}");
        Log::debug("      Confirmations: " . ($txData['confirmations'] ?? 0));
        Log::debug("      Address (from node): " . ($txData['address'] ?? 'N/A'));
        Log::debug("      Wallet ID: {$wallet->id}");
        Log::debug("      Wallet Name: {$wallet->name}");

        // Check if transaction already exists
        $existingTx = BtcTransaction::where('txid', $txData['txid'])
            ->where('btc_wallet_id', $wallet->id)
            ->first();

        if ($existingTx) {
            Log::debug("      Transaction exists in DB (ID: {$existingTx->id}), checking for updates");
            // Update confirmations for existing transaction
            $this->updateExistingTransaction($existingTx, $txData);
            return;
        }

        // Determine transaction type
        $type = match ($txData['category']) {
            'receive' => 'deposit',
            // TODO: remove 'generate' if not using mining or testing
            'generate' => 'deposit',
            'send' => 'withdrawal',
            default => null
        };

        Log::debug("      Mapped type: " . ($type ?? 'null (will skip)'));

        // Skip unsupported transaction types
        if (!$type) {
            Log::debug("      Skipping unsupported transaction category: {$txData['category']}");
            return;
        }

        // Find the address associated with this transaction
        $btcAddressId = null;
        if (isset($txData['address'])) {
            Log::debug("      Looking up address in wallet's addresses...");
            $btcAddress = $wallet->addresses()->where('address', $txData['address'])->first();
            if ($btcAddress) {
                $btcAddressId = $btcAddress->id;
                Log::debug("      ✓ Found address in DB: {$txData['address']} (ID: {$btcAddressId})");
            } else {
                Log::warning("      ✗ Address not in DB: {$txData['address']} - transaction will still be recorded but without address link");
                Log::debug("      Wallet has " . $wallet->addresses()->count() . " addresses in DB");
                if ($wallet->addresses()->count() > 0) {
                    $existingAddresses = $wallet->addresses()->pluck('address')->toArray();
                    Log::debug("      Existing addresses: " . implode(', ', $existingAddresses));
                }
            }
        } else {
            Log::warning("      No 'address' field in transaction data!");
        }

        Log::debug("      Creating NEW transaction record: type={$type}, amount=" . abs($txData['amount']));

        $confirmations = $txData['confirmations'] ?? 0;
        $requiredConfirmations = config('bitcoinrpc.confirmations_required', 6);
        $isConfirmed = $confirmations >= $requiredConfirmations;

        // Calculate USD value at time of transaction
        $btcAmount = abs($txData['amount']);
        $usdValue = null;
        try {
            $btcRate = \App\Models\ExchangeRate::where('crypto_shortname', 'btc')->first();
            if ($btcRate) {
                $usdValue = $btcAmount * $btcRate->usd_rate;
                Log::debug("      Calculated USD value: \${$usdValue} (rate: \${$btcRate->usd_rate} per BTC)");
            }
        } catch (\Exception $e) {
            Log::warning("      Failed to calculate USD value: " . $e->getMessage());
        }

        // Create transaction record
        $transaction = BtcTransaction::create([
            'btc_wallet_id' => $wallet->id,
            'btc_address_id' => $btcAddressId,
            'txid' => $txData['txid'],
            'type' => $type,
            'amount' => $btcAmount,
            'usd_value' => $usdValue,
            'fee' => abs($txData['fee'] ?? 0),
            'confirmations' => $confirmations,
            'status' => $isConfirmed ? 'confirmed' : 'pending',
            'raw_transaction' => $txData,
            'block_hash' => $txData['blockhash'] ?? null,
            'confirmed_at' => $isConfirmed ? now() : null
        ]);

        Log::info("New BTC transaction detected: {$transaction->txid} ({$type}) for {$transaction->amount} BTC on wallet {$wallet->name}");

        // Mark address as used if this is a deposit
        if ($type === 'deposit' && $btcAddressId) {
            $btcAddress = BtcAddress::find($btcAddressId);
            if ($btcAddress && !$btcAddress->is_used) {
                $btcAddress->markAsUsed();
                Log::debug("      Marked address as used: {$btcAddress->address}");
            }
        }

        // Process confirmation if already confirmed
        if ($transaction->status === 'confirmed') {
            Log::debug("      Transaction already confirmed, processing confirmation");
            Log::debug("      This will trigger balance update and main wallet sync");
            $transaction->processConfirmation();
            Log::debug("      Confirmation processing completed");
        } else {
            Log::debug("      Transaction is pending (confirmations: {$transaction->confirmations})");
        }

        Log::debug("      === Transaction Processing Complete ===");
    }

    /**
     * Update existing transaction with new confirmation data.
     */
    private function updateExistingTransaction(BtcTransaction $transaction, array $txData): void
    {
        $oldConfirmations = $transaction->confirmations;
        $newConfirmations = $txData['confirmations'] ?? 0;
        $requiredConfirmations = config('bitcoinrpc.confirmations_required', 6);

        // Only update if confirmations have changed
        if ($oldConfirmations === $newConfirmations) {
            Log::debug("          No confirmation change ({$oldConfirmations} confirmations)");
            return;
        }

        // Always update confirmations count, but skip status change if below threshold
        // This allows tracking of confirmation progress even before meeting threshold
        if ($transaction->status === 'pending' && $newConfirmations < $requiredConfirmations) {
            // Update confirmation count but keep pending status
            $transaction->update([
                'confirmations' => $newConfirmations,
                'block_hash' => $txData['blockhash'] ?? $transaction->block_hash,
                'block_height' => $txData['blockheight'] ?? $transaction->block_height,
            ]);
            Log::debug("          Updated confirmations ({$oldConfirmations} -> {$newConfirmations}) but keeping pending status (threshold: {$requiredConfirmations})");
            return;
        }

        // Process full confirmation update (will trigger status change and balance update)
        Log::debug("          Updating confirmations: {$oldConfirmations} -> {$newConfirmations}");

        $transaction->updateConfirmations(
            $newConfirmations,
            $txData['blockhash'] ?? $transaction->block_hash,
            $txData['blockheight'] ?? $transaction->block_height
        );

        Log::info("Updated BTC transaction {$transaction->txid}: {$newConfirmations} confirmations");
    }

    /**
     * Get current Bitcoin price in USD.
     */
    public static function getCurrentPrice(): float
    {
        try {
            // You can use an API like CoinGecko or your preferred price source
            $response = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd');
            $data = json_decode($response, true);

            return $data['bitcoin']['usd'] ?? 0;

        } catch (\Exception $e) {
            Log::error("Failed to fetch BTC price: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Convert BTC to USD.
     */
    public static function convertToUsd(float $btcAmount): float
    {
        $price = static::getCurrentPrice();
        return $btcAmount * $price;
    }

    /**
     * Get wallet info from Bitcoin node.
     */
    public function getWalletInfo(string $walletName): array
    {
        try {
            return $this->client->getWalletInfo($walletName);
        } catch (\Exception $e) {
            Log::error("Failed to get wallet info for {$walletName}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new wallet on Bitcoin node.
     */
    public function createWallet(string $walletName): bool
    {
        try {
            $this->client->createWallet($walletName);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to create wallet {$walletName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate new address from Bitcoin node.
     */
    public function getNewAddress(string $walletName): ?string
    {
        try {
            return $this->client->wallet($walletName)->getNewAddress();
        } catch (\Exception $e) {
            Log::error("Failed to generate address for {$walletName}: " . $e->getMessage());
            return null;
        }
    }

    // search wallet using client
    public function searchWallet(string $walletName): bool
    {
        try {
            $wallets = $this->client->listWallets();
            return in_array($walletName, $wallets->toArray());
        } catch (\Exception $e) {
            Log::error("Failed to search wallet {$walletName}: " . $e->getMessage());
            return false;
        }
    }

    public function generateBTCWallet(string $name) : void
    {
        $repository = new static();
        if (!$repository->searchWallet($name)) {
            $repository->createWallet($name);
        }
    }

    /**
     * Get wallet balance from Bitcoin node.
     *
     * @param string $walletName
     * @return float|null Balance in BTC, null on failure
     */
    public function getWalletBalance(string $walletName): ?float
    {
        try {
            $wallet = $this->client->wallet($walletName);
            $balanceResponse = $wallet->getBalance();
            $balance = is_object($balanceResponse) && method_exists($balanceResponse, 'result')
                ? (float) $balanceResponse->result()
                : (float) $balanceResponse;

            return $balance;
        } catch (\Exception $e) {
            Log::error("Failed to get balance for wallet {$walletName}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get appropriate fee rate (sat/vB) based on transaction amount.
     *
     * @param float $amount Amount in BTC
     * @return int Fee rate in satoshis per virtual byte
     */
    public static function getFeeRateForAmount(float $amount): int
    {
        $feeTiers = config('bitcoinrpc.fee_tiers', [
            ['min' => 0.0001, 'max' => 0.001, 'rate' => 5],
            ['min' => 0.001, 'max' => 0.01, 'rate' => 10],
            ['min' => 0.01, 'max' => 0.1, 'rate' => 20],
            ['min' => 0.1, 'max' => 1.0, 'rate' => 30],
            ['min' => 1.0, 'max' => null, 'rate' => 50],
        ]);

        foreach ($feeTiers as $tier) {
            if ($amount >= $tier['min'] && ($tier['max'] === null || $amount < $tier['max'])) {
                return $tier['rate'];
            }
        }

        // Default fallback (should never reach here with proper config)
        return 10;
    }

    /**
     * Estimate transaction fee based on number of outputs and fee rate.
     *
     * @param int $numOutputs Number of outputs in the transaction
     * @param int $feeRate Fee rate in satoshis per virtual byte
     * @return float Estimated fee in BTC
     */
    public static function estimateTransactionFee(int $numOutputs, int $feeRate): float
    {
        // Estimate transaction size in vBytes
        // Typical sizes: input ~148 vB, output ~34 vB, overhead ~10 vB
        $numInputs = 1; // Assuming 1 input for simplicity; adjust as needed
        $txSize = (148 * $numInputs) + (34 * $numOutputs) + 10;

        // Calculate fee in satoshis
        $feeSatoshis = $txSize * $feeRate;

        // Convert to BTC
        return $feeSatoshis / 100_000_000;
    }

    /**
     * Send Bitcoin from user's wallet to address.
     *
     * @param string $walletName User's wallet name (username_pri)
     * @param string $address Recipient Bitcoin address
     * @param float $amount Amount in BTC
     * @param bool $subtractFeeFromAmount If true, fee is deducted from amount (sender pays less)
     * @return string|null Transaction ID or null on failure
     */
    /**
     * Send Bitcoin from wallet to address.
     *
     * @param string $walletName
     * @param string $address
     * @param float $amount
     * @param bool $subtractFeeFromAmount If true, deduct fee from amount instead of adding on top
     * @return string|null Transaction ID on success, null on failure
     */
    public static function sendBitcoin(string $walletName, string $address, float $amount, bool $subtractFeeFromAmount = false): ?string
    {
        $repository = new self();

        try {
            // Validate wallet exists
            if (!$repository->searchWallet($walletName)) {
                Log::error("Wallet not found: {$walletName}");
                return null;
            }

            // Load wallet
            $wallet = $repository->client->wallet($walletName);

            // Check available balance
            $balanceResponse = $wallet->getBalance();
            $balance = is_object($balanceResponse) && method_exists($balanceResponse, 'result')
                ? (float) $balanceResponse->result()
                : (float) $balanceResponse;

            if ($balance < $amount) {
                Log::error("Insufficient balance in wallet {$walletName}: {$balance} < {$amount}");
                return null;
            }

            // Calculate fee rate based on transaction amount using fee tiers
            $feeRate = static::getFeeRateForAmount($amount);

            // Send transaction with calculated fee rate
            // Parameters: address, amount, comment, comment_to, subtractFee, replaceable, conf_target, estimate_mode, avoid_reuse, fee_rate
            $txidResponse = $wallet->sendToAddress(
                $address,
                $amount,
                '',                      // comment
                '',                      // comment_to
                $subtractFeeFromAmount,  // subtractfeefromamount - deduct fee from amount if true
                true,                    // replaceable (BIP125)
                null,                    // conf_target
                'unset',                 // estimate_mode
                null,                    // avoid_reuse
                $feeRate                 // fee_rate in sat/vB
            );
            $txid = is_object($txidResponse) && method_exists($txidResponse, 'result')
                ? $txidResponse->result()
                : $txidResponse;

            if (!$txid) {
                Log::error("Failed to send Bitcoin from {$walletName} to {$address}");
                return null;
            }

            Log::info("Bitcoin sent successfully", [
                'wallet' => $walletName,
                'to' => $address,
                'amount' => $amount,
                'fee_rate' => $feeRate,
                'txid' => $txid,
            ]);

            return $txid;

        } catch (\Exception $e) {
            Log::error("Error sending Bitcoin: " . $e->getMessage(), [
                'wallet' => $walletName,
                'address' => $address,
                'amount' => $amount,
            ]);
            return null;
        }
    }
}
