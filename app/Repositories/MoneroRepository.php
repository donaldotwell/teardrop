<?php

namespace App\Repositories;

use App\Exceptions\MoneroRpcException;
use App\Models\User;
use App\Models\XmrWallet;
use App\Models\XmrAddress;
use App\Models\XmrTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class MoneroRepository
{
    private string $rpcUrl;
    private string $rpcUser;
    private string $rpcPassword;

    public function __construct()
    {
        $this->rpcUrl = config('monero.scheme') . '://' .
                        config('monero.host') . ':' .
                        config('monero.port') . '/json_rpc';
        $this->rpcUser = config('monero.user');
        $this->rpcPassword = config('monero.password');
    }

    /**
     * Check if Monero RPC service is available.
     */
    public function isRpcAvailable(): bool
    {
        try {
            $response = Http::withBasicAuth($this->rpcUser, $this->rpcPassword)
                ->timeout(5)
                ->post($this->rpcUrl, [
                    'jsonrpc' => '2.0',
                    'id' => '0',
                    'method' => 'get_version',
                    'params' => [],
                ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Monero RPC availability check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique wallet password for a user.
     */
    private function generateWalletPassword(User $user): string
    {
        // Create unique password from user credentials + app key
        return hash('sha256', $user->id . $user->password . config('app.key'));
    }

    /**
     * Make RPC call to monero-wallet-rpc.
     */
    private function rpcCall(string $method, array $params = [])
    {
        try {
            $response = Http::withBasicAuth($this->rpcUser, $this->rpcPassword)
                ->timeout(30)
                ->post($this->rpcUrl, [
                    'jsonrpc' => '2.0',
                    'id' => '0',
                    'method' => $method,
                    'params' => $params,
                ]);

            if (!$response->successful()) {
                Log::error("Monero RPC call failed: {$method}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new MoneroRpcException(
                    "RPC call failed with status {$response->status()}",
                    $response->status(),
                    ['method' => $method, 'params' => $params]
                );
            }

            $data = $response->json();

            if (isset($data['error'])) {
                Log::error("Monero RPC error: {$method}", $data['error']);
                throw new MoneroRpcException(
                    $data['error']['message'] ?? 'Unknown RPC error',
                    $data['error']['code'] ?? 0,
                    ['method' => $method, 'params' => $params, 'error' => $data['error']]
                );
            }

            return $data['result'] ?? null;

        } catch (MoneroRpcException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Monero RPC exception: {$method} - " . $e->getMessage());
            throw new MoneroRpcException(
                "RPC call exception: " . $e->getMessage(),
                0,
                ['method' => $method, 'params' => $params]
            );
        }
    }

    /**
     * Create or get Monero wallet for user.
     */
    public static function getOrCreateWalletForUser(User $user): XmrWallet
    {
        $existingWallet = $user->xmrWallet;

        if ($existingWallet) {
            return $existingWallet;
        }

        $repository = new static();

        // Check RPC availability first
        if (!$repository->isRpcAvailable()) {
            throw new MoneroRpcException('Monero RPC service is not available. Please contact support.');
        }

        $walletName = $user->username_pri . '.wallet';
        $password = $repository->generateWalletPassword($user);
        $passwordHash = hash('sha256', $password);

        // Try to open existing wallet first
        try {
            $opened = $repository->openWallet($walletName, $password);

            if ($opened) {
                // Get address and keys from opened wallet
                $addressData = $repository->getAddress();
                $viewKey = $repository->getViewKey();
                $spendKey = $repository->getSpendKey();
                $seed = $repository->getSeed();

                if (!$addressData) {
                    throw new MoneroRpcException("Failed to get address from existing wallet");
                }

                // Create wallet record with all data
                $wallet = XmrWallet::create([
                    'user_id' => $user->id,
                    'name' => $walletName,
                    'primary_address' => $addressData['address'],
                    'view_key' => $viewKey,
                    'spend_key_encrypted' => $spendKey ? Crypt::encryptString($spendKey) : null,
                    'seed_encrypted' => $seed ? Crypt::encryptString($seed) : null,
                    'password_hash' => $passwordHash,
                    'height' => 0,
                    'balance' => 0,
                    'unlocked_balance' => 0,
                    'total_received' => 0,
                    'total_sent' => 0,
                    'is_active' => true,
                ]);

                // Create primary address record
                $wallet->addresses()->create([
                    'address' => $addressData['address'],
                    'account_index' => 0,
                    'address_index' => 0,
                    'label' => 'Primary Address',
                    'balance' => 0,
                    'total_received' => 0,
                    'tx_count' => 0,
                    'is_used' => false,
                ]);

                Log::info("Opened existing Monero wallet for user {$user->id}", [
                    'wallet_name' => $walletName,
                    'address' => $addressData['address'],
                ]);

                return $wallet;
            }
        } catch (MoneroRpcException $e) {
            // Wallet doesn't exist or can't be opened, proceed to create new one
            Log::debug("Wallet doesn't exist, creating new one: " . $e->getMessage());
        }

        // Create new wallet
        $walletData = $repository->createWallet($walletName, $password);

        if (!$walletData) {
            throw new MoneroRpcException("Failed to create Monero wallet for user {$user->id}");
        }

        // Create wallet record
        $wallet = XmrWallet::create([
            'user_id' => $user->id,
            'name' => $walletName,
            'primary_address' => $walletData['address'],
            'view_key' => $walletData['view_key'] ?? null,
            'spend_key_encrypted' => isset($walletData['spend_key']) ? Crypt::encryptString($walletData['spend_key']) : null,
            'seed_encrypted' => isset($walletData['seed']) ? Crypt::encryptString($walletData['seed']) : null,
            'password_hash' => $passwordHash,
            'height' => $walletData['height'] ?? 0,
            'balance' => 0,
            'unlocked_balance' => 0,
            'total_received' => 0,
            'total_sent' => 0,
            'is_active' => true,
        ]);

        // Create primary address record
        $wallet->addresses()->create([
            'address' => $walletData['address'],
            'account_index' => 0,
            'address_index' => 0,
            'label' => 'Primary Address',
            'balance' => 0,
            'total_received' => 0,
            'tx_count' => 0,
            'is_used' => false,
        ]);

        Log::info("Created Monero wallet for user {$user->id}", [
            'wallet_name' => $walletName,
            'address' => $walletData['address'],
        ]);

        return $wallet;
    }

    /**
     * Open existing wallet.
     */
    public function openWallet(string $filename, string $password): bool
    {
        try {
            $result = $this->rpcCall('open_wallet', [
                'filename' => $filename,
                'password' => $password,
            ]);

            return $result !== null;
        } catch (MoneroRpcException $e) {
            // Wallet doesn't exist or wrong password
            Log::debug("Failed to open wallet {$filename}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create new wallet.
     */
    public function createWallet(string $filename, string $password): ?array
    {
        // Get current blockchain height for restore_height
        $heightData = $this->getCurrentHeight();
        $currentHeight = $heightData['height'] ?? 0;

        // Create the wallet
        $result = $this->rpcCall('create_wallet', [
            'filename' => $filename,
            'password' => $password,
            'language' => 'English',
            'restore_height' => $currentHeight, // Don't scan old blocks for new wallet
        ]);

        if (!$result) {
            throw new MoneroRpcException("Failed to create wallet: {$filename}");
        }

        // Get the primary address
        $addressData = $this->getAddress();

        if (!$addressData) {
            throw new MoneroRpcException("Failed to get address after creating wallet");
        }

        // Get the mnemonic seed (CRITICAL for recovery)
        $seed = $this->getSeed();

        // Get view and spend keys
        $viewKey = $this->getViewKey();
        $spendKey = $this->getSpendKey();

        Log::info("Created new Monero wallet", [
            'filename' => $filename,
            'address' => $addressData['address'],
            'height' => $currentHeight,
            'has_seed' => !empty($seed),
        ]);

        return [
            'address' => $addressData['address'],
            'seed' => $seed,
            'view_key' => $viewKey,
            'spend_key' => $spendKey,
            'height' => $currentHeight,
        ];
    }

    /**
     * Close current wallet.
     */
    public function closeWallet(): bool
    {
        $result = $this->rpcCall('close_wallet');
        return $result !== null;
    }

    /**
     * Get mnemonic seed from currently opened wallet.
     */
    public function getSeed(): ?string
    {
        try {
            $result = $this->rpcCall('query_key', ['key_type' => 'mnemonic']);
            return $result['key'] ?? null;
        } catch (MoneroRpcException $e) {
            Log::warning("Failed to get mnemonic seed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get view key from currently opened wallet.
     */
    public function getViewKey(): ?string
    {
        try {
            $result = $this->rpcCall('query_key', ['key_type' => 'view_key']);
            return $result['key'] ?? null;
        } catch (MoneroRpcException $e) {
            Log::warning("Failed to get view key: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get spend key from currently opened wallet.
     */
    public function getSpendKey(): ?string
    {
        try {
            $result = $this->rpcCall('query_key', ['key_type' => 'spend_key']);
            return $result['key'] ?? null;
        } catch (MoneroRpcException $e) {
            Log::warning("Failed to get spend key: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current blockchain height.
     */
    public function getCurrentHeight(): array
    {
        try {
            $result = $this->rpcCall('get_height');
            return ['height' => $result['height'] ?? 0];
        } catch (MoneroRpcException $e) {
            Log::warning("Failed to get blockchain height: " . $e->getMessage());
            return ['height' => 0];
        }
    }

    /**
     * Get wallet balance.
     */
    public static function getBalance(string $walletName): ?array
    {
        $repository = new static();

        // Get wallet from database to retrieve password hash
        $wallet = XmrWallet::where('name', $walletName)->first();

        if (!$wallet || !$wallet->user) {
            Log::error("Wallet not found or user missing for balance check: {$walletName}");
            return null;
        }

        $password = $repository->generateWalletPassword($wallet->user);

        // Open wallet
        if (!$repository->openWallet($walletName, $password)) {
            Log::error("Failed to open wallet for balance: {$walletName}");
            return null;
        }

        $result = $repository->rpcCall('get_balance', [
            'account_index' => 0,
        ]);

        if (!$result) {
            return null;
        }

        // Convert from atomic units (piconero) to XMR
        return [
            'balance' => $result['balance'] / 1e12,
            'unlocked_balance' => $result['unlocked_balance'] / 1e12,
        ];
    }

    /**
     * Get primary address.
     */
    public function getAddress(int $accountIndex = 0, int $addressIndex = 0): ?array
    {
        $result = $this->rpcCall('get_address', [
            'account_index' => $accountIndex,
            'address_index' => [$addressIndex],
        ]);

        if (!$result || !isset($result['address'])) {
            return null;
        }

        return [
            'address' => $result['address'],
            'addresses' => $result['addresses'] ?? [],
        ];
    }

    /**
     * Create subaddress.
     */
    public static function createSubaddress(string $walletName, int $accountIndex = 0, ?string $label = null): ?array
    {
        $repository = new static();

        // Get wallet from database to retrieve password
        $wallet = XmrWallet::where('name', $walletName)->first();

        if (!$wallet || !$wallet->user) {
            Log::error("Wallet not found or user missing for subaddress: {$walletName}");
            return null;
        }

        $password = $repository->generateWalletPassword($wallet->user);

        // Open wallet
        if (!$repository->openWallet($walletName, $password)) {
            Log::error("Failed to open wallet for subaddress: {$walletName}");
            return null;
        }

        $result = $repository->rpcCall('create_address', [
            'account_index' => $accountIndex,
            'label' => $label ?? '',
        ]);

        if (!$result) {
            return null;
        }

        return [
            'address' => $result['address'],
            'address_index' => $result['address_index'],
        ];
    }

    /**
     * Get incoming transfers.
     */
    public function getIncomingTransfers(int $accountIndex = 0): array
    {
        $result = $this->rpcCall('get_transfers', [
            'in' => true,
            'out' => false,
            'pending' => true,
            'failed' => false,
            'pool' => true,
            'account_index' => $accountIndex,
        ]);

        if (!$result) {
            return [];
        }

        $transfers = [];

        foreach (['in', 'pending', 'pool'] as $type) {
            if (isset($result[$type])) {
                $transfers = array_merge($transfers, $result[$type]);
            }
        }

        return $transfers;
    }

    /**
     * Get all transfers (incoming and outgoing).
     */
    public function getAllTransfers(int $accountIndex = 0): array
    {
        $result = $this->rpcCall('get_transfers', [
            'in' => true,
            'out' => true,
            'pending' => true,
            'failed' => true,
            'pool' => true,
            'account_index' => $accountIndex,
        ]);

        if (!$result) {
            return [];
        }

        $transfers = [];

        foreach (['in', 'out', 'pending', 'failed', 'pool'] as $type) {
            if (isset($result[$type])) {
                foreach ($result[$type] as $tx) {
                    $tx['transfer_type'] = $type;
                    $transfers[] = $tx;
                }
            }
        }

        return $transfers;
    }

    /**
     * Send Monero transaction.
     */
    public static function transfer(string $walletName, string $address, float $amount): ?string
    {
        $repository = new static();

        // Get wallet from database to retrieve password
        $wallet = XmrWallet::where('name', $walletName)->first();

        if (!$wallet || !$wallet->user) {
            Log::error("Wallet not found or user missing for transfer: {$walletName}");
            return null;
        }

        $password = $repository->generateWalletPassword($wallet->user);

        // Open wallet
        if (!$repository->openWallet($walletName, $password)) {
            Log::error("Failed to open Monero wallet: {$walletName}");
            return null;
        }

        // Convert XMR to atomic units (piconero)
        $atomicAmount = (int) round($amount * 1e12);

        $result = $repository->rpcCall('transfer', [
            'destinations' => [
                [
                    'amount' => $atomicAmount,
                    'address' => $address,
                ],
            ],
            'account_index' => 0,
            'priority' => 1, // Default priority
            'get_tx_key' => true,
        ]);

        if (!$result || !isset($result['tx_hash'])) {
            Log::error("Failed to send Monero from {$walletName} to {$address}");
            return null;
        }

        Log::info("Monero sent successfully", [
            'wallet' => $walletName,
            'to' => $address,
            'amount' => $amount,
            'tx_hash' => $result['tx_hash'],
        ]);

        return $result['tx_hash'];
    }

    /**
     * Sync all active Monero wallets.
     */
    public static function syncAllWallets(): void
    {
        Log::debug("=== Starting Monero wallet sync ===");

        $activeWallets = XmrWallet::where('is_active', true)->get();

        Log::debug("Found {$activeWallets->count()} active Monero wallets to sync");

        foreach ($activeWallets as $wallet) {
            Log::debug("Syncing wallet ID: {$wallet->id}, Name: {$wallet->name}, User ID: {$wallet->user_id}");
            static::syncWalletTransactions($wallet);
        }

        Log::debug("=== Monero wallet sync completed ===");
    }

    /**
     * Sync transactions for specific wallet.
     */
    public static function syncWalletTransactions(XmrWallet $wallet): void
    {
        try {
            Log::debug("  Wallet {$wallet->id}: Starting sync");

            $repository = new static();

            if (!$wallet->user) {
                Log::error("User missing for wallet {$wallet->id}, cannot sync");
                return;
            }

            $password = $repository->generateWalletPassword($wallet->user);

            // Open wallet
            if (!$repository->openWallet($wallet->name, $password)) {
                Log::error("Failed to open wallet {$wallet->name} for sync");
                return;
            }

            // Get all transfers
            $transfers = $repository->getAllTransfers();

            Log::debug("  Found " . count($transfers) . " transfer(s) from Monero RPC");

            // Process each transfer
            foreach ($transfers as $tx) {
                $repository->processWalletTransaction($wallet, $tx);
            }

            // Update wallet balance
            $wallet->updateBalance();

            Log::debug("  Wallet {$wallet->id}: Sync completed");

        } catch (\Exception $e) {
            Log::error("Failed to sync Monero wallet {$wallet->id}: " . $e->getMessage());
        }
    }

    /**
     * Process individual transaction for a wallet.
     */
    private function processWalletTransaction(XmrWallet $wallet, array $txData): void
    {
        $txHash = $txData['txid'] ?? null;

        if (!$txHash) {
            Log::warning("Transaction missing txid", $txData);
            return;
        }

        // Determine transaction type
        $type = match ($txData['transfer_type']) {
            'in' => 'deposit',
            'out' => 'withdrawal',
            default => null,
        };

        if (!$type) {
            Log::debug("Skipping unsupported transaction type: " . ($txData['transfer_type'] ?? 'unknown'));
            return;
        }

        // Check if transaction already exists
        $existingTx = XmrTransaction::where('txid', $txHash)
            ->where('xmr_wallet_id', $wallet->id)
            ->first();

        if ($existingTx) {
            // Update confirmations
            $this->updateExistingTransaction($existingTx, $txData);
            return;
        }

        // Convert from atomic units to XMR
        $amount = ($txData['amount'] ?? 0) / 1e12;
        $fee = ($txData['fee'] ?? 0) / 1e12;

        // Find associated address
        $xmrAddressId = null;
        if (isset($txData['address'])) {
            $xmrAddress = $wallet->addresses()->where('address', $txData['address'])->first();
            if ($xmrAddress) {
                $xmrAddressId = $xmrAddress->id;
            }
        }

        // Determine status based on confirmation thresholds
        $confirmations = $txData['confirmations'] ?? 0;
        $unlockTime = $txData['unlock_time'] ?? 0;
        $minConfirmations = config('monero.min_confirmations', 10);

        $status = 'pending';
        if ($confirmations > 0 && $confirmations < $minConfirmations) {
            $status = 'confirmed';
        } elseif ($confirmations >= $minConfirmations) {
            $status = 'unlocked';
        }

        // Create transaction record
        $transaction = XmrTransaction::create([
            'xmr_wallet_id' => $wallet->id,
            'xmr_address_id' => $xmrAddressId,
            'txid' => $txHash,
            'payment_id' => $txData['payment_id'] ?? null,
            'type' => $type,
            'amount' => $amount,
            'fee' => $fee,
            'confirmations' => $confirmations,
            'unlock_time' => $unlockTime,
            'height' => $txData['height'] ?? null,
            'status' => $status,
            'raw_transaction' => $txData,
            'confirmed_at' => $confirmations > 0 ? now() : null,
            'unlocked_at' => $status === 'unlocked' ? now() : null,
        ]);

        Log::info("New XMR transaction detected: {$txHash} ({$type}) for {$amount} XMR");

        // Process confirmation if unlocked
        if ($status === 'unlocked') {
            $transaction->processConfirmation();
        }
    }

    /**
     * Update existing transaction with new confirmation data.
     */
    private function updateExistingTransaction(XmrTransaction $transaction, array $txData): void
    {
        $oldConfirmations = $transaction->confirmations;
        $newConfirmations = $txData['confirmations'] ?? 0;
        $minConfirmations = config('monero.min_confirmations', 10);

        // Only update if confirmations have changed
        if ($oldConfirmations === $newConfirmations) {
            Log::debug("          No confirmation change ({$oldConfirmations} confirmations)");
            return;
        }

        // Always update confirmations count, but track progress toward unlock threshold
        if ($transaction->status === 'pending' && $newConfirmations > 0 && $newConfirmations < $minConfirmations) {
            // Update to confirmed but not yet unlocked
            $transaction->update([
                'confirmations' => $newConfirmations,
                'status' => 'confirmed',
                'height' => $txData['height'] ?? $transaction->height,
                'confirmed_at' => $transaction->confirmed_at ?? now(),
            ]);
            Log::debug("          Updated confirmations ({$oldConfirmations} -> {$newConfirmations}) to confirmed status (unlock threshold: {$minConfirmations})");
            return;
        }

        // Process full confirmation update (may trigger unlock and balance update)
        $transaction->updateConfirmations(
            $newConfirmations,
            $txData['height'] ?? $transaction->height
        );

        Log::info("Updated XMR transaction {$transaction->txid}: {$newConfirmations} confirmations");
    }

    /**
     * Get current Monero price in USD.
     */
    public static function getCurrentPrice(): float
    {
        try {
            $response = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=monero&vs_currencies=usd');
            $data = json_decode($response, true);

            return $data['monero']['usd'] ?? 0;

        } catch (\Exception $e) {
            Log::error("Failed to fetch XMR price: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Convert XMR to USD.
     */
    public static function convertToUsd(float $xmrAmount): float
    {
        $price = static::getCurrentPrice();
        return $xmrAmount * $price;
    }
}
