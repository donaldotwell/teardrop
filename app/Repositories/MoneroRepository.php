<?php

namespace App\Repositories;

use App\Exceptions\MoneroRpcException;
use App\Models\User;
use App\Models\XmrWallet;
use App\Models\XmrAddress;
use App\Models\XmrTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

/**
 * Monero RPC repository — per-user wallet architecture.
 *
 * monero-wallet-rpc runs with --wallet-dir, meaning any wallet file in the
 * directory can be opened/closed on demand. Only ONE wallet can be open at
 * a time, so every operation follows:
 *
 *   acquireLock -> open_wallet -> interact -> store -> close_wallet -> releaseLock
 *
 * The withWallet() helper encapsulates this lifecycle.
 */
class MoneroRepository
{
    private string $rpcUrl;
    private string $rpcUser;
    private string $rpcPassword;

    // --- Construction ---

    public function __construct()
    {
        $this->rpcUrl = config('monero.scheme') . '://' .
                        config('monero.host') . ':' .
                        config('monero.port') . '/json_rpc';
        $this->rpcUser = config('monero.user', '');
        $this->rpcPassword = config('monero.password', '');
    }

    // --- RPC Primitives ---

    /**
     * Check if Monero RPC service is available.
     */
    public function isRpcAvailable(): bool
    {
        try {
            $request = Http::timeout(5);

            if (!empty($this->rpcUser)) {
                $request = $request->withBasicAuth($this->rpcUser, $this->rpcPassword);
            }

            $response = $request->post($this->rpcUrl, [
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
     * Make JSON-RPC call to monero-wallet-rpc.
     */
    public function rpcCall(string $method, array $params = [])
    {
        try {
            $request = Http::timeout(30);

            if (!empty($this->rpcUser)) {
                $request = $request->withBasicAuth($this->rpcUser, $this->rpcPassword);
            }

            $response = $request->post($this->rpcUrl, [
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

    // --- Wallet Lifecycle (open / close / lock) ---

    /**
     * Execute a callback with a specific wallet opened and locked.
     *
     * This is the CORE pattern for the per-wallet architecture:
     *   1. Acquire global cache lock (only one wallet at a time on RPC)
     *   2. open_wallet(filename, password)
     *   3. refresh() — sync to chain tip
     *   4. Execute $callback($this) — caller does work
     *   5. store() — persist wallet state to disk
     *   6. close_wallet()
     *   7. Release lock
     *
     * @param string   $walletName Wallet filename (no extension needed)
     * @param string   $password   Plaintext wallet password
     * @param \Closure $callback   fn(MoneroRepository $repo): mixed
     * @param bool     $refresh    Whether to call refresh() after opening (default true)
     * @return mixed               Whatever $callback returns
     */
    public function withWallet(string $walletName, string $password, \Closure $callback, bool $refresh = true): mixed
    {
        $lockTimeout = config('monero.rpc_lock_timeout', 60);
        $waitTimeout = config('monero.rpc_lock_wait_timeout', 30);

        $lock = Cache::lock('monero:rpc:wallet', $lockTimeout);

        Log::debug("[withWallet] Acquiring lock for wallet '{$walletName}'...");

        $acquired = $lock->block($waitTimeout);

        if (!$acquired) {
            throw new MoneroRpcException(
                "Could not acquire Monero RPC lock after {$waitTimeout}s — another operation is in progress",
                0,
                ['wallet' => $walletName]
            );
        }

        try {
            // Close any wallet that might be left open from a previous crash
            try {
                $this->rpcCall('close_wallet');
            } catch (\Exception $e) {
                // Expected if no wallet open — ignore
            }

            // Open the target wallet
            $this->rpcCall('open_wallet', [
                'filename' => $walletName,
                'password' => $password,
            ]);

            Log::debug("[withWallet] Opened wallet '{$walletName}'");

            // Sync wallet to chain tip
            if ($refresh) {
                $this->rpcCall('refresh');
                Log::debug("[withWallet] Refreshed wallet '{$walletName}'");
            }

            // Execute caller's work
            $result = $callback($this);

            // Persist wallet state to disk
            $this->rpcCall('store');

            // Close wallet
            $this->rpcCall('close_wallet');
            Log::debug("[withWallet] Closed wallet '{$walletName}'");

            return $result;

        } catch (\Exception $e) {
            // Best-effort close on error
            try {
                $this->rpcCall('close_wallet');
            } catch (\Exception $closeEx) {
                Log::warning("[withWallet] Failed to close wallet after error: " . $closeEx->getMessage());
            }

            Log::error("[withWallet] Error with wallet '{$walletName}': " . $e->getMessage());
            throw $e;

        } finally {
            $lock->release();
            Log::debug("[withWallet] Released lock for wallet '{$walletName}'");
        }
    }

    /**
     * Execute a callback with the XmrWallet model's wallet opened.
     * Convenience wrapper that decrypts the password automatically.
     *
     * @param XmrWallet $wallet
     * @param \Closure  $callback fn(MoneroRepository $repo): mixed
     * @param bool      $refresh  Whether to refresh after opening
     * @return mixed
     */
    public function withWalletModel(XmrWallet $wallet, \Closure $callback, bool $refresh = true): mixed
    {
        $password = $wallet->getDecryptedPassword();

        return $this->withWallet($wallet->name, $password, $callback, $refresh);
    }

    // --- Wallet Creation ---

    /**
     * Generate a deterministic wallet password for a given identifier.
     * The password is a SHA-256 hash of (id + salt + APP_KEY).
     *
     * @param string|int $identifier  User ID, order ID, or any unique key
     * @return string                 Raw plaintext password (not encrypted yet)
     */
    public static function generateWalletPassword(string|int $identifier): string
    {
        return hash('sha256', $identifier . config('monero.wallet_password_salt', '') . config('app.key'));
    }

    /**
     * Create a new wallet file on monero-wallet-rpc.
     * Returns wallet data (address, seed, keys, height).
     * The wallet is left CLOSED after creation.
     *
     * @param string $walletName  Unique filename for the wallet
     * @param string $password    Plaintext password
     * @return array              ['address', 'seed', 'view_key', 'spend_key', 'height']
     */
    public function createWalletFile(string $walletName, string $password): array
    {
        $lockTimeout = config('monero.rpc_lock_timeout', 60);
        $waitTimeout = config('monero.rpc_lock_wait_timeout', 30);

        $lock = Cache::lock('monero:rpc:wallet', $lockTimeout);
        $acquired = $lock->block($waitTimeout);

        if (!$acquired) {
            throw new MoneroRpcException(
                "Could not acquire Monero RPC lock for wallet creation",
                0,
                ['wallet' => $walletName]
            );
        }

        try {
            // Close any open wallet first
            try {
                $this->rpcCall('close_wallet');
            } catch (\Exception $e) {
                // Ignore
            }

            // Create the wallet file (auto-opens it)
            try {
                $this->rpcCall('create_wallet', [
                    'filename' => $walletName,
                    'password' => $password,
                    'language' => 'English',
                ]);
            } catch (MoneroRpcException $e) {
                // Wallet file already exists on disk — open it instead
                if ($e->getCode() === -21) {
                    Log::info("Wallet file '{$walletName}' already exists on RPC, opening it instead");
                    $this->rpcCall('open_wallet', [
                        'filename' => $walletName,
                        'password' => $password,
                    ]);
                    $this->rpcCall('refresh');
                } else {
                    throw $e;
                }
            }

            // Get current blockchain height
            $heightData = $this->rpcCall('get_height');
            $currentHeight = $heightData['height'] ?? 0;

            // Primary address (account 0, index 0)
            $addressData = $this->rpcCall('get_address', [
                'account_index' => 0,
            ]);

            $address = $addressData['address'] ?? null;

            if (!$address) {
                throw new MoneroRpcException("Failed to get address after creating wallet '{$walletName}'");
            }

            // Mnemonic seed (critical for recovery)
            $seed = null;
            try {
                $seedResult = $this->rpcCall('query_key', ['key_type' => 'mnemonic']);
                $seed = $seedResult['key'] ?? null;
            } catch (\Exception $e) {
                Log::warning("Failed to get seed for wallet '{$walletName}': " . $e->getMessage());
            }

            // View key
            $viewKey = null;
            try {
                $viewResult = $this->rpcCall('query_key', ['key_type' => 'view_key']);
                $viewKey = $viewResult['key'] ?? null;
            } catch (\Exception $e) {
                Log::warning("Failed to get view key for wallet '{$walletName}': " . $e->getMessage());
            }

            // Spend key
            $spendKey = null;
            try {
                $spendResult = $this->rpcCall('query_key', ['key_type' => 'spend_key']);
                $spendKey = $spendResult['key'] ?? null;
            } catch (\Exception $e) {
                Log::warning("Failed to get spend key for wallet '{$walletName}': " . $e->getMessage());
            }

            // Persist and close
            $this->rpcCall('store');
            $this->rpcCall('close_wallet');

            Log::info("Created new Monero wallet file", [
                'filename' => $walletName,
                'address' => $address,
                'height' => $currentHeight,
                'has_seed' => !empty($seed),
            ]);

            return [
                'address' => $address,
                'seed' => $seed,
                'view_key' => $viewKey,
                'spend_key' => $spendKey,
                'height' => $currentHeight,
            ];

        } catch (\Exception $e) {
            try {
                $this->rpcCall('close_wallet');
            } catch (\Exception $closeEx) {
                // Ignore
            }

            Log::error("Failed to create wallet '{$walletName}': " . $e->getMessage());
            throw $e;

        } finally {
            $lock->release();
        }
    }

    /**
     * Get or create Monero wallet for a user.
     *
     * Each user gets their own wallet file (e.g. 'user_42').
     * If the user has no wallet, a new file is created on the RPC,
     * and DB records (XmrWallet + initial XmrAddress) are written.
     *
     * @param User $user
     * @return XmrWallet
     */
    public static function getOrCreateWalletForUser(User $user): XmrWallet
    {
        $existingWallet = $user->xmrWallet;

        if ($existingWallet) {
            return $existingWallet;
        }

        $repository = new static();

        // Check RPC availability
        if (!$repository->isRpcAvailable()) {
            throw new MoneroRpcException('Monero RPC service is not available. Please contact support.');
        }

        // Wallet filename: "user_{id}" — unique per user
        $walletName = 'user_' . $user->id;

        // Deterministic password, encrypted for DB storage
        $rawPassword = static::generateWalletPassword('user_' . $user->id);
        $encryptedPassword = Crypt::encryptString($rawPassword);

        // Create the wallet file on RPC
        $walletData = $repository->createWalletFile($walletName, $rawPassword);

        // Create wallet DB record
        $wallet = XmrWallet::create([
            'user_id' => $user->id,
            'name' => $walletName,
            'primary_address' => $walletData['address'],
            'view_key' => $walletData['view_key'],
            'spend_key_encrypted' => $walletData['spend_key'] ? Crypt::encryptString($walletData['spend_key']) : null,
            'seed_encrypted' => $walletData['seed'] ? Crypt::encryptString($walletData['seed']) : null,
            'password_encrypted' => $encryptedPassword,
            'height' => $walletData['height'],
            'balance' => 0,
            'unlocked_balance' => 0,
            'total_received' => 0,
            'total_sent' => 0,
            'is_active' => true,
        ]);

        // Create initial address record (primary address = account 0, index 0)
        $wallet->addresses()->create([
            'address' => $walletData['address'],
            'account_index' => 0,
            'address_index' => 0,
            'label' => "User {$user->id} - primary",
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

    // --- Balance ---

    /**
     * Get live RPC balance for a wallet (opens wallet, queries, closes).
     *
     * @param XmrWallet $wallet
     * @return array ['balance' => float XMR, 'unlocked_balance' => float XMR]
     */
    public function getWalletBalance(XmrWallet $wallet): array
    {
        return $this->withWalletModel($wallet, function (self $repo) {
            return $repo->getOpenWalletBalance();
        });
    }

    /**
     * Get balance from an already-open wallet session (no open/close).
     * Only call this INSIDE a withWallet() callback.
     *
     * @return array ['balance' => float XMR, 'unlocked_balance' => float XMR]
     */
    public function getOpenWalletBalance(): array
    {
        $result = $this->rpcCall('get_balance', [
            'account_index' => 0,
        ]);

        return [
            'balance' => ($result['balance'] ?? 0) / 1e12,
            'unlocked_balance' => ($result['unlocked_balance'] ?? 0) / 1e12,
        ];
    }

    // --- Address Management ---

    /**
     * Create a new receiving subaddress in a wallet.
     * Opens the wallet, creates the address, closes it.
     *
     * @param XmrWallet   $wallet
     * @param string|null $label
     * @return array ['address' => string, 'address_index' => int]
     */
    public function createAddress(XmrWallet $wallet, ?string $label = null): array
    {
        return $this->withWalletModel($wallet, function (self $repo) use ($wallet, $label) {
            return $repo->createAddressInOpenWallet($label ?? "Wallet {$wallet->id} - " . time());
        });
    }

    /**
     * Create a new address inside an already-open wallet session.
     * Only call this INSIDE a withWallet() callback.
     *
     * @param string|null $label
     * @return array ['address' => string, 'address_index' => int]
     */
    public function createAddressInOpenWallet(?string $label = null): array
    {
        $result = $this->rpcCall('create_address', [
            'account_index' => 0,
            'label' => $label ?? 'Address ' . time(),
        ]);

        if (!$result || !isset($result['address'])) {
            throw new MoneroRpcException("Failed to create address in open wallet");
        }

        return [
            'address' => $result['address'],
            'address_index' => $result['address_index'],
        ];
    }

    // --- Transfers ---

    /**
     * Send XMR from a wallet to one destination.
     * Opens wallet -> transfer -> close.
     *
     * @param XmrWallet $wallet      Source wallet
     * @param string    $destination  Destination Monero address
     * @param float     $amount       Amount in XMR
     * @return array ['tx_hash', 'fee', 'amount', 'tx_key']
     */
    public function transfer(XmrWallet $wallet, string $destination, float $amount): array
    {
        return $this->withWalletModel($wallet, function (self $repo) use ($wallet, $destination, $amount) {
            $atomicAmount = (int) round($amount * 1e12);

            $result = $repo->rpcCall('transfer', [
                'destinations' => [
                    ['amount' => $atomicAmount, 'address' => $destination],
                ],
                'account_index' => 0,
                'priority' => 1,
                'get_tx_key' => true,
            ]);

            if (!$result || !isset($result['tx_hash'])) {
                throw new MoneroRpcException("Transfer failed from wallet '{$wallet->name}'");
            }

            Log::info("XMR transfer sent", [
                'wallet_id' => $wallet->id,
                'to' => $destination,
                'amount' => $amount,
                'tx_hash' => $result['tx_hash'],
                'fee' => ($result['fee'] ?? 0) / 1e12,
            ]);

            return [
                'tx_hash' => $result['tx_hash'],
                'fee' => ($result['fee'] ?? 0) / 1e12,
                'amount' => ($result['amount'] ?? 0) / 1e12,
                'tx_key' => $result['tx_key'] ?? null,
            ];
        });
    }

    /**
     * Send XMR from a wallet to multiple destinations in a single transaction.
     * Supports subtract_fee_from_outputs to deduct the network fee from
     * specific destination(s) instead of requiring extra funds.
     *
     * @param XmrWallet $wallet
     * @param array     $destinations             [['address' => string, 'amount' => float XMR], ...]
     * @param array     $subtractFeeFromOutputs   Output indices to deduct fee from (e.g. [0])
     * @return array ['tx_hash', 'fee', 'amount', 'tx_key']
     */
    public function transferMulti(XmrWallet $wallet, array $destinations, array $subtractFeeFromOutputs = []): array
    {
        return $this->withWalletModel($wallet, function (self $repo) use ($wallet, $destinations, $subtractFeeFromOutputs) {
            // Convert to atomic units
            $rpcDestinations = [];
            $totalAmount = 0;

            foreach ($destinations as $dest) {
                $atomicAmount = (int) round($dest['amount'] * 1e12);
                $rpcDestinations[] = [
                    'amount' => $atomicAmount,
                    'address' => $dest['address'],
                ];
                $totalAmount += $dest['amount'];
            }

            $params = [
                'destinations' => $rpcDestinations,
                'account_index' => 0,
                'priority' => 1,
                'get_tx_key' => true,
            ];

            // subtract_fee_from_outputs: deduct network fee from specified destination indices
            if (!empty($subtractFeeFromOutputs)) {
                $params['subtract_fee_from_outputs'] = $subtractFeeFromOutputs;
            }

            $result = $repo->rpcCall('transfer', $params);

            if (!$result || !isset($result['tx_hash'])) {
                throw new MoneroRpcException("Multi-transfer failed from wallet '{$wallet->name}'");
            }

            Log::info("XMR multi-transfer sent", [
                'wallet_id' => $wallet->id,
                'num_destinations' => count($destinations),
                'total_amount' => $totalAmount,
                'tx_hash' => $result['tx_hash'],
                'fee' => ($result['fee'] ?? 0) / 1e12,
                'subtract_fee_from' => $subtractFeeFromOutputs,
            ]);

            return [
                'tx_hash' => $result['tx_hash'],
                'fee' => ($result['fee'] ?? 0) / 1e12,
                'amount' => ($result['amount'] ?? 0) / 1e12,
                'tx_key' => $result['tx_key'] ?? null,
            ];
        });
    }

    /**
     * Sweep ALL funds from a wallet to a single destination address.
     * The entire balance minus the network fee is sent.
     * Ideal for escrow refunds (single recipient gets everything).
     *
     * @param XmrWallet $wallet
     * @param string    $destination
     * @return array ['tx_hash', 'fee', 'amount', 'tx_key']
     */
    public function sweepAll(XmrWallet $wallet, string $destination): array
    {
        return $this->withWalletModel($wallet, function (self $repo) use ($wallet, $destination) {
            $result = $repo->rpcCall('sweep_all', [
                'address' => $destination,
                'account_index' => 0,
                'priority' => 1,
                'get_tx_key' => true,
            ]);

            if (!$result || !isset($result['tx_hash_list'][0])) {
                throw new MoneroRpcException("sweep_all failed from wallet '{$wallet->name}'");
            }

            $txHash = $result['tx_hash_list'][0];
            $fee = ($result['fee_list'][0] ?? 0) / 1e12;
            $amount = ($result['amount_list'][0] ?? 0) / 1e12;
            $txKey = $result['tx_key_list'][0] ?? null;

            Log::info("XMR sweep_all sent", [
                'wallet_id' => $wallet->id,
                'to' => $destination,
                'amount' => $amount,
                'fee' => $fee,
                'tx_hash' => $txHash,
            ]);

            return [
                'tx_hash' => $txHash,
                'fee' => $fee,
                'amount' => $amount,
                'tx_key' => $txKey,
            ];
        });
    }

    // --- Sync ---

    /**
     * Sync a single wallet: open -> refresh -> get_transfers -> process -> update DB -> close.
     *
     * @param XmrWallet $wallet
     */
    public function syncWallet(XmrWallet $wallet): void
    {
        $this->withWalletModel($wallet, function (self $repo) use ($wallet) {
            Log::debug("Syncing wallet {$wallet->id} ({$wallet->name})");

            // Get current blockchain height
            $heightData = $repo->rpcCall('get_height');
            $currentHeight = $heightData['height'] ?? 0;

            // Get balance from RPC (authoritative)
            $balanceResult = $repo->rpcCall('get_balance', ['account_index' => 0]);
            $balance = ($balanceResult['balance'] ?? 0) / 1e12;
            $unlockedBalance = ($balanceResult['unlocked_balance'] ?? 0) / 1e12;

            // Get all transfers
            $params = [
                'in' => true,
                'out' => true,
                'pending' => true,
                'failed' => false,
                'pool' => true,
                'account_index' => 0,
            ];

            // Use min_height to avoid re-processing old blocks
            if ($wallet->height > 0) {
                $params['min_height'] = $wallet->height;
            }

            $transfersResult = $repo->rpcCall('get_transfers', $params);

            // Collect all transfers
            $allTransfers = [];
            foreach (['in', 'out', 'pending', 'pool'] as $type) {
                if (isset($transfersResult[$type])) {
                    foreach ($transfersResult[$type] as $tx) {
                        $tx['transfer_type'] = $type;
                        $allTransfers[] = $tx;
                    }
                }
            }

            Log::debug("Wallet {$wallet->id}: height={$currentHeight}, " . count($allTransfers) . " transfers, balance={$balance}, unlocked={$unlockedBalance}");

            // Process each transaction
            foreach ($allTransfers as $tx) {
                $repo->processTransaction($wallet, $tx);
            }

            // Reconcile stale pending/confirmed transactions
            $staleTransactions = XmrTransaction::where('xmr_wallet_id', $wallet->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('created_at', '<', now()->subMinutes(10))
                ->get();

            foreach ($staleTransactions as $staleTx) {
                try {
                    $result = $repo->rpcCall('get_transfer_by_txid', [
                        'txid' => $staleTx->txid,
                        'account_index' => 0,
                    ]);

                    $rpcTx = null;
                    if (isset($result['transfer'])) {
                        $rpcTx = $result['transfer'];
                    } elseif (isset($result['transfers']) && !empty($result['transfers'])) {
                        foreach ($result['transfers'] as $entry) {
                            $entryType = $entry['type'] ?? '';
                            if ($staleTx->type === 'withdrawal' && $entryType === 'out') {
                                $rpcTx = $entry;
                                break;
                            } elseif ($staleTx->type === 'deposit' && $entryType === 'in') {
                                $rpcTx = $entry;
                                break;
                            }
                        }
                        $rpcTx = $rpcTx ?? $result['transfers'][0];
                    }

                    if ($rpcTx) {
                        $rpcTx['transfer_type'] = $rpcTx['type'] ?? ($staleTx->type === 'withdrawal' ? 'out' : 'in');
                        $repo->updateExistingTransaction($staleTx, $rpcTx);
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to reconcile stale tx " . substr($staleTx->txid, 0, 16) . "...: " . $e->getMessage());
                }
            }

            // Address rotation: if current address is used, create a new one
            $currentAddress = $wallet->getCurrentAddress();
            if (!$currentAddress) {
                Log::debug("Wallet {$wallet->id}: No unused address, creating new one");
                $newAddr = $repo->createAddressInOpenWallet("User {$wallet->user_id} - " . time());

                $wallet->addresses()->create([
                    'address' => $newAddr['address'],
                    'account_index' => 0,
                    'address_index' => $newAddr['address_index'],
                    'label' => "User {$wallet->user_id} - " . time(),
                    'balance' => 0,
                    'total_received' => 0,
                    'tx_count' => 0,
                    'is_used' => false,
                ]);
            }

            // Update wallet DB record with authoritative RPC values
            $wallet->update([
                'height' => $currentHeight,
                'balance' => $balance,
                'unlocked_balance' => $unlockedBalance,
                'last_synced_at' => now(),
            ]);

            // Update aggregate stats
            $totalReceived = $wallet->transactions()
                ->where('type', 'deposit')
                ->whereIn('status', ['confirmed', 'unlocked'])
                ->sum('amount');

            $totalSent = $wallet->transactions()
                ->where('type', 'withdrawal')
                ->where('status', '!=', 'failed')
                ->sum('amount');

            $wallet->update([
                'total_received' => $totalReceived,
                'total_sent' => abs($totalSent),
            ]);

            Log::debug("Wallet {$wallet->id} sync complete");
        });
    }

    /**
     * Sync an escrow wallet (has its own wallet file, no user_id).
     *
     * @param \App\Models\EscrowWallet $escrowWallet
     */
    public function syncEscrowWallet(\App\Models\EscrowWallet $escrowWallet): void
    {
        if ($escrowWallet->currency !== 'xmr') {
            return;
        }

        // Find the underlying XmrWallet record
        $xmrWallet = XmrWallet::where('name', $escrowWallet->wallet_name)->first();

        if (!$xmrWallet) {
            $xmrWallet = XmrWallet::where('primary_address', $escrowWallet->address)->first();
        }

        if (!$xmrWallet) {
            Log::warning("No XmrWallet found for escrow wallet {$escrowWallet->id}");
            return;
        }

        // Sync the underlying wallet
        $this->syncWallet($xmrWallet);

        // Update escrow balance from the synced wallet
        $escrowWallet->update([
            'balance' => $xmrWallet->fresh()->unlocked_balance,
        ]);

        // Check if escrow is now funded
        if (!$escrowWallet->order->escrow_funded_at && $escrowWallet->balance > 0) {
            $escrowWallet->order->update([
                'escrow_funded_at' => now(),
            ]);
            Log::info("Escrow wallet funded for order #{$escrowWallet->order_id}");
        }
    }

    // --- Transaction Processing ---

    /**
     * Process a single transaction from get_transfers.
     * Creates or updates XmrTransaction record.
     *
     * @param XmrWallet $wallet
     * @param array     $txData  Single transfer entry from RPC
     */
    private function processTransaction(XmrWallet $wallet, array $txData): void
    {
        $txHash = $txData['txid'] ?? null;

        if (!$txHash) {
            return;
        }

        // Determine type
        $type = match ($txData['transfer_type']) {
            'in' => 'deposit',
            'out' => 'withdrawal',
            'pending' => isset($txData['type']) && $txData['type'] === 'out' ? 'withdrawal' : 'deposit',
            'pool' => isset($txData['type']) && $txData['type'] === 'out' ? 'withdrawal' : 'deposit',
            default => null,
        };

        if (!$type) {
            return;
        }

        $confirmations = $txData['confirmations'] ?? 0;
        $minConfirmations = config('monero.min_confirmations', 10);

        // Check for existing transaction (unique: txid + wallet_id + type)
        $existingTx = XmrTransaction::where('txid', $txHash)
            ->where('xmr_wallet_id', $wallet->id)
            ->where('type', $type)
            ->first();

        if ($existingTx) {
            $this->updateExistingTransaction($existingTx, $txData);
            return;
        }

        // Convert from atomic units
        $amount = ($txData['amount'] ?? 0) / 1e12;
        if ($amount == 0 && isset($txData['destinations'][0]['amount'])) {
            $amount = $txData['destinations'][0]['amount'] / 1e12;
        }
        $fee = ($txData['fee'] ?? 0) / 1e12;

        // Match to an XmrAddress if possible
        $xmrAddressId = null;
        $xmrAddress = null;

        if (isset($txData['subaddr_index']['minor'])) {
            $addrIndex = $txData['subaddr_index']['minor'];
            $xmrAddress = $wallet->addresses()->where('address_index', $addrIndex)->first();
            if ($xmrAddress) {
                $xmrAddressId = $xmrAddress->id;
            }
        }

        if ($xmrAddress === null && isset($txData['address'])) {
            $xmrAddress = $wallet->addresses()->where('address', $txData['address'])->first();
            if ($xmrAddress) {
                $xmrAddressId = $xmrAddress->id;
            }
        }

        // Mark address as used on first deposit
        if ($xmrAddress !== null && !$xmrAddress->is_used && $type === 'deposit') {
            $xmrAddress->markAsUsed();
        }

        // USD value
        $usdValue = null;
        try {
            $xmrRate = \App\Models\ExchangeRate::where('crypto_shortname', 'xmr')->first();
            if ($xmrRate) {
                $usdValue = $amount * $xmrRate->usd_rate;
            }
        } catch (\Exception $e) {
            // Non-critical
        }

        // Status from confirmations
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
            'usd_value' => $usdValue,
            'fee' => $fee,
            'confirmations' => $confirmations,
            'unlock_time' => $txData['unlock_time'] ?? 0,
            'height' => $txData['height'] ?? null,
            'status' => $status,
            'raw_transaction' => $txData,
            'confirmed_at' => $confirmations > 0 ? now() : null,
            'unlocked_at' => $status === 'unlocked' ? now() : null,
        ]);

        Log::debug("New XMR tx: {$txHash} ({$type}) {$amount} XMR on wallet {$wallet->id}");

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

        // Fill in USD value if missing
        if ($transaction->usd_value === null || $transaction->usd_value == 0) {
            try {
                $xmrRate = \App\Models\ExchangeRate::where('crypto_shortname', 'xmr')->first();
                if ($xmrRate) {
                    $transaction->update(['usd_value' => $transaction->amount * $xmrRate->usd_rate]);
                }
            } catch (\Exception $e) {
                // Non-critical
            }
        }

        if ($oldConfirmations === $newConfirmations) {
            return;
        }

        // Prevent downgrade in force-confirmation mode
        if (config('monero.force_confirmations')) {
            $statusPriority = ['pending' => 1, 'confirmed' => 2, 'unlocked' => 3];
            $currentPriority = $statusPriority[$transaction->status] ?? 0;

            $blockchainStatus = 'pending';
            if ($newConfirmations > 0 && $newConfirmations < $minConfirmations) {
                $blockchainStatus = 'confirmed';
            } elseif ($newConfirmations >= $minConfirmations) {
                $blockchainStatus = 'unlocked';
            }

            if (($statusPriority[$blockchainStatus] ?? 0) < $currentPriority) {
                return;
            }
        }

        // Already fully confirmed
        if ($oldConfirmations >= $minConfirmations) {
            return;
        }

        // Update pending -> confirmed
        if ($transaction->status === 'pending' && $newConfirmations > 0 && $newConfirmations < $minConfirmations) {
            $transaction->update([
                'confirmations' => $newConfirmations,
                'status' => 'confirmed',
                'height' => $txData['height'] ?? $transaction->height,
                'confirmed_at' => $transaction->confirmed_at ?? now(),
            ]);
            return;
        }

        // Process full confirmation update (may trigger unlock)
        $transaction->updateConfirmations(
            $newConfirmations,
            $txData['height'] ?? $transaction->height
        );
    }

    // --- Helpers ---

    /**
     * Get current blockchain height from within an open wallet session.
     *
     * @return int
     */
    public function getCurrentHeight(): int
    {
        try {
            $result = $this->rpcCall('get_height');
            return $result['height'] ?? 0;
        } catch (\Exception $e) {
            Log::warning("Failed to get blockchain height: " . $e->getMessage());
            return 0;
        }
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
        return $xmrAmount * static::getCurrentPrice();
    }
}
