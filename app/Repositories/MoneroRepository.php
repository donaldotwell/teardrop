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
     * Get master wallet name from config.
     */
    private function getMasterWalletName(): string
    {
        return config('monero.master_wallet_name', 'teardrop_master');
    }

    /**
     * Get master wallet password from config.
     */
    private function getMasterWalletPassword(): string
    {
        return config('monero.master_wallet_password') ?? hash('sha256', config('app.key'));
    }

    /**
     * Generate unique wallet password for a user.
     * @deprecated Use master wallet with subaddresses instead
     */
    private function generateWalletPassword(User $user): string
    {
        // Create unique password from user credentials + app key
        return hash('sha256', $user->id . $user->password . config('app.key'));
    }

    /**
     * Make RPC call to monero-wallet-rpc.
     */
    public function rpcCall(string $method, array $params = [])
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
     * Create or get Monero wallet for user using subaddress from master wallet.
     * NEW ARCHITECTURE: One master wallet with subaddresses (0/0, 0/1, 0/2...) instead of separate wallet files.
     */
    public static function getOrCreateWalletForUser(User $user): XmrWallet
    {
        $existingWallet = $user->xmrWallet;

        if ($existingWallet) {
            // Ensure wallet has at least one UNUSED address (safety check)
            $hasUnusedAddress = $existingWallet->addresses()->where('is_used', false)->exists();
            
            if (!$hasUnusedAddress) {
                Log::warning("Wallet {$existingWallet->id} for user {$user->id} has no unused addresses, creating one");
                
                $repository = new static();
                
                // Create new subaddress in master wallet
                $subaddressData = $repository->rpcCall('create_address', [
                    'account_index' => 0,
                    'label' => "User {$user->id} - {$user->username_pri}",
                ]);

                if ($subaddressData && isset($subaddressData['address'])) {
                    $existingWallet->addresses()->create([
                        'address' => $subaddressData['address'],
                        'account_index' => 0,
                        'address_index' => $subaddressData['address_index'],
                        'label' => "User {$user->id} - {$user->username_pri}",
                        'balance' => 0,
                        'total_received' => 0,
                        'tx_count' => 0,
                        'is_used' => false,
                    ]);
                    
                    // Update primary address if not set
                    if (!$existingWallet->primary_address) {
                        $existingWallet->update(['primary_address' => $subaddressData['address']]);
                    }
                }
            }
            
            return $existingWallet;
        }

        $repository = new static();

        // Check RPC availability first
        if (!$repository->isRpcAvailable()) {
            throw new MoneroRpcException('Monero RPC service is not available. Please contact support.');
        }

        $masterWalletName = $repository->getMasterWalletName();

        // Create new subaddress in master wallet
        // RPC automatically assigns next sequential index (0, 1, 2, 3...)
        $subaddressData = $repository->rpcCall('create_address', [
            'account_index' => 0,
            'label' => "User {$user->id} - {$user->username_pri}",
        ]);

        if (!$subaddressData || !isset($subaddressData['address'])) {
            throw new MoneroRpcException("Failed to create subaddress for user {$user->id}");
        }

        $address = $subaddressData['address'];
        $addressIndex = $subaddressData['address_index'];

        // Create wallet record (using master wallet name but storing user's subaddress)
        $wallet = XmrWallet::create([
            'user_id' => $user->id,
            'name' => $masterWalletName, // All users share same wallet name
            'primary_address' => $address,
            'view_key' => null, // Master wallet keys not stored per-user
            'spend_key_encrypted' => null,
            'seed_encrypted' => null,
            'password_hash' => null, // No per-user password needed
            'height' => 0,
            'balance' => 0,
            'unlocked_balance' => 0,
            'total_received' => 0,
            'total_sent' => 0,
            'is_active' => true,
        ]);

        // Create address record for the newly created subaddress
        $wallet->addresses()->create([
            'address' => $address,
            'account_index' => 0,
            'address_index' => $addressIndex,
            'label' => "User {$user->id} - {$user->username_pri}",
            'balance' => 0,
            'total_received' => 0,
            'tx_count' => 0,
            'is_used' => false,
        ]);

        Log::debug("Created Monero subaddress for user {$user->id}", [
            'master_wallet' => $masterWalletName,
            'address_index' => $addressIndex,
            'address' => $address,
        ]);

        return $wallet;
    }

    /**
     * Open existing wallet.
     */
    public function openWallet(string $filename, string $password): bool
    {
        try {
            // CRITICAL: wallet-rpc can only have ONE wallet loaded at a time
            // Close any currently open wallet first to avoid conflicts
            try {
                $this->closeWallet();
                Log::debug("Closed any previously open wallet before opening {$filename}");
            } catch (\Exception $e) {
                // Ignore errors if no wallet was open
                Log::debug("No wallet to close (this is normal): " . $e->getMessage());
            }

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
        // CRITICAL: wallet-rpc can only have ONE wallet loaded at a time
        // Close any currently open wallet first
        try {
            $this->closeWallet();
            Log::debug("Closed any previously open wallet before creating {$filename}");
        } catch (\Exception $e) {
            // Ignore errors if no wallet was open
            Log::debug("No wallet to close before creation (this is normal): " . $e->getMessage());
        }

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
     * Get balance for specific wallet.
     * NEW ARCHITECTURE: Master wallet is already loaded, query by address indices.
     */
    public static function getBalance(string $walletName): ?array
    {
        $repository = new static();

        // Find wallet record to get address
        $wallet = XmrWallet::where('name', $walletName)->first();

        if (!$wallet) {
            Log::error("Wallet not found for balance check: {$walletName}");
            return null;
        }

        // Master wallet is already loaded, get balance for specific address
        // For subaddress architecture, we need to get balance by address_index
        $address = $wallet->addresses()->first();
        
        if (!$address) {
            Log::error("No address found for wallet: {$walletName}");
            return null;
        }

        // Query balance by account and address indices (NO wallet opening/closing!)
        $result = $repository->rpcCall('get_balance', [
            'account_index' => $address->account_index,
            'address_indices' => [$address->address_index],
        ]);

        if (!$result) {
            return null;
        }

        // Convert from atomic units (piconero) to XMR
        $perSubaddress = $result['per_subaddress'][0] ?? null;
        
        if (!$perSubaddress) {
            return [
                'balance' => 0,
                'unlocked_balance' => 0,
            ];
        }

        return [
            'balance' => $perSubaddress['balance'] / 1e12,
            'unlocked_balance' => $perSubaddress['unlocked_balance'] / 1e12,
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
     * Create subaddress in master wallet.
     * NEW ARCHITECTURE: Master wallet already loaded, just create address.
     */
    public static function createSubaddress(string $walletName, int $accountIndex = 0, ?string $label = null): ?array
    {
        $repository = new static();

        // Master wallet should already be loaded
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
     * Get balance for a specific address by index.
     * Returns both locked and unlocked balance.
     */
    public static function getAddressBalance(int $accountIndex, int $addressIndex): ?array
    {
        $repository = new static();

        try {
            $result = $repository->rpcCall('get_balance', [
                'account_index' => $accountIndex,
                'address_indices' => [$addressIndex],
            ]);

            if (!$result || !isset($result['per_subaddress'][0])) {
                return null;
            }

            $subaddress = $result['per_subaddress'][0];

            return [
                'balance' => $subaddress['balance'] / 1e12,
                'unlocked_balance' => $subaddress['unlocked_balance'] / 1e12,
                'num_unspent_outputs' => $subaddress['num_unspent_outputs'] ?? 0,
                'address_index' => $subaddress['address_index'],
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get address balance for {$accountIndex}/{$addressIndex}", [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Find addresses with sufficient combined balance to cover amount.
     * Returns array of address indices that should be swept.
     */
    public static function findAddressesForPayment(XmrWallet $wallet, float $amount): array
    {
        // CRITICAL: Don't filter by xmr_addresses.balance (it's DEPRECATED and stale)
        // Instead, query ALL addresses and check their RPC balance
        $addresses = $wallet->addresses()
            ->orderBy('address_index', 'asc')
            ->get();

        $selectedAddresses = [];
        $totalCollected = 0;

        foreach ($addresses as $address) {
            // Verify current balance from RPC (authoritative source)
            $balanceData = self::getAddressBalance($address->account_index, $address->address_index);
            
            if (!$balanceData || $balanceData['unlocked_balance'] <= 0) {
                continue;
            }

            $selectedAddresses[] = [
                'address_index' => $address->address_index,
                'account_index' => $address->account_index,
                'balance' => $balanceData['unlocked_balance'],
            ];

            $totalCollected += $balanceData['unlocked_balance'];

            // Stop once we have enough
            if ($totalCollected >= $amount) {
                break;
            }
        }

        if ($totalCollected < $amount) {
            Log::warning("Insufficient total balance for payment", [
                'wallet_id' => $wallet->id,
                'needed' => $amount,
                'collected' => $totalCollected,
                'addresses_checked' => count($addresses),
                'addresses_with_funds' => count($selectedAddresses),
            ]);
            return [];
        }

        Log::info("Found sufficient addresses for payment", [
            'wallet_id' => $wallet->id,
            'needed' => $amount,
            'collected' => $totalCollected,
            'addresses_selected' => count($selectedAddresses),
            'address_indices' => array_column($selectedAddresses, 'address_index'),
        ]);

        return $selectedAddresses;
    }

    /**
     * Sweep funds from multiple addresses to a destination.
     * This consolidates funds from multiple subaddresses into one payment.
     */
    public static function sweepAddresses(array $sourceAddressIndices, int $accountIndex, string $destination, float $amount): ?array
    {
        $repository = new static();

        if (empty($sourceAddressIndices)) {
            Log::error("No source addresses provided for sweep");
            return null;
        }

        try {
            // Convert XMR to atomic units
            $atomicAmount = (int) round($amount * 1e12);

            Log::info("Sweeping addresses for payment", [
                'source_indices' => $sourceAddressIndices,
                'account_index' => $accountIndex,
                'destination' => $destination,
                'amount_xmr' => $amount,
                'amount_atomic' => $atomicAmount,
            ]);

            // Use transfer with multiple subaddr_indices
            $result = $repository->rpcCall('transfer', [
                'destinations' => [
                    [
                        'amount' => $atomicAmount,
                        'address' => $destination,
                    ],
                ],
                'account_index' => $accountIndex,
                'subaddr_indices' => $sourceAddressIndices,
                'priority' => 1,
                'get_tx_key' => true,
            ]);

            if (!$result || !isset($result['tx_hash'])) {
                Log::error("Failed to sweep addresses", [
                    'source_indices' => $sourceAddressIndices,
                    'result' => $result,
                ]);
                return null;
            }

            Log::info("Successfully swept addresses", [
                'tx_hash' => $result['tx_hash'],
                'fee' => ($result['fee'] ?? 0) / 1e12,
                'amount_sent' => $result['amount'] / 1e12,
            ]);

            return [
                'tx_hash' => $result['tx_hash'],
                'fee' => ($result['fee'] ?? 0) / 1e12,
                'amount' => $result['amount'] / 1e12,
                'tx_key' => $result['tx_key'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error("Exception during address sweep", [
                'error' => $e->getMessage(),
                'source_indices' => $sourceAddressIndices,
                'destination' => $destination,
            ]);
            return null;
        }
    }

    /**
     * Send Monero transaction from specific subaddress.
     * NEW ARCHITECTURE: Master wallet already loaded, specify account/address index.
     */
    public static function transfer(string $walletName, string $address, float $amount): ?string
    {
        $repository = new static();

        // Get wallet record to find subaddress indices
        $wallet = XmrWallet::where('name', $walletName)->first();

        if (!$wallet) {
            Log::error("Wallet not found for transfer: {$walletName}");
            return null;
        }

        // Get address record to find account/address indices
        $addressRecord = $wallet->addresses()->first();
        
        if (!$addressRecord) {
            Log::error("No address found for wallet: {$walletName}");
            return null;
        }

        // Master wallet should already be loaded
        // Convert XMR to atomic units (piconero)
        $atomicAmount = (int) round($amount * 1e12);

        $result = $repository->rpcCall('transfer', [
            'destinations' => [
                [
                    'amount' => $atomicAmount,
                    'address' => $address,
                ],
            ],
            'account_index' => $addressRecord->account_index,
            'subaddr_indices' => [$addressRecord->address_index],
            'priority' => 1, // Default priority
            'get_tx_key' => true,
        ]);

        if (!$result || !isset($result['tx_hash'])) {
            Log::error("Failed to send Monero from {$walletName} to {$address}");
            return null;
        }

        Log::debug("Monero sent successfully", [
            'wallet' => $walletName,
            'from_address' => $addressRecord->address,
            'to' => $address,
            'amount' => $amount,
            'tx_hash' => $result['tx_hash'],
        ]);

        return $result['tx_hash'];
    }

    /**
     * Sync all active Monero wallets from master wallet.
     * OPTIMIZED: Makes ONE RPC call for all addresses using height-based filtering.
     * Discovery: subaddr_indices: [0] returns ALL subaddresses, no need to query individually.
     */
    public static function syncAllWallets(): void
    {
        Log::debug("=== Starting Monero wallet sync (single RPC call optimization) ===");

        $repository = new static();

        // Get current blockchain height
        $heightData = $repository->getCurrentHeight();
        $currentHeight = $heightData['height'] ?? 0;
        Log::debug("Current blockchain height: {$currentHeight}");

        // Get all active addresses across all wallets that need syncing
        // OPTIMIZATION: Only sync addresses that need checking:
        // 1. Addresses that are not marked as used (is_used = false) - still expecting funds
        // 2. Addresses with recent transactions (last 30 days) - may have new confirmations
        $activeAddresses = XmrAddress::whereHas('wallet', function ($query) {
                $query->where('is_active', true);
            })
            ->where(function ($q) {
                $q->where('is_used', false)
                  ->orWhere('last_used_at', '>=', now()->subDays(30));
            })
            ->with('wallet')
            ->get();

        if ($activeAddresses->isEmpty()) {
            Log::debug("No active addresses to sync");
            return;
        }

        Log::debug("Found {$activeAddresses->count()} active addresses across all wallets");

        // Find MINIMUM last_synced_height across all addresses (to catch any that haven't synced)
        $minHeight = $activeAddresses->min('last_synced_height') ?? 0;
        Log::debug("Using minimum sync height: {$minHeight}");

        try {
            // Make ONE RPC call for ALL addresses
            $params = [
                'in' => true,
                'out' => true,
                'pending' => true,
                'failed' => false,
                'pool' => true,
                'account_index' => 0,
                // Omit subaddr_indices to get ALL subaddresses in account 0
            ];
            
            // Only filter by height if we have a previous sync point
            if ($minHeight > 0) {
                $params['min_height'] = $minHeight;
                $params['max_height'] = $currentHeight;
            }
            
            Log::debug("Making single RPC call for all addresses (min_height: {$minHeight})");
            $transfersResult = $repository->rpcCall('get_transfers', $params);
            
            // Process all transfer types and tag with type
            $allTransfers = [];
            foreach (['in', 'out', 'pending', 'pool'] as $type) {
                if (isset($transfersResult[$type])) {
                    foreach ($transfersResult[$type] as $tx) {
                        $tx['transfer_type'] = $type;
                        $allTransfers[] = $tx;
                    }
                }
            }
            
            Log::debug("Received " . count($allTransfers) . " total transfer(s) from RPC");

            // Build address lookup map for efficient matching
            $addressLookup = [];
            foreach ($activeAddresses as $addr) {
                $addressLookup[$addr->address] = $addr;
            }

            // Process transactions and distribute to appropriate addresses/wallets
            $processedCount = 0;
            $walletsToUpdate = [];
            
            foreach ($allTransfers as $tx) {
                $txHash = $tx['txid'] ?? 'unknown';
                $confirmations = $tx['confirmations'] ?? 0;
                $minConfirmations = config('monero.min_confirmations', 10);
                
                // Skip transactions that haven't met minimum confirmations yet (unless in testing mode)
                if (!config('monero.force_confirmations') && $confirmations < $minConfirmations) {
                    Log::debug("  Skipping tx " . substr($txHash, 0, 16) . "... with only {$confirmations} confirmations (need {$minConfirmations})");
                    continue;
                }
                
                // CASE 1: Transaction TO one of our tracked addresses (incoming)
                if (isset($tx['address']) && isset($addressLookup[$tx['address']])) {
                    $targetAddress = $addressLookup[$tx['address']];
                    $wallet = $targetAddress->wallet;
                    Log::debug("  Matched {$tx['transfer_type']} tx to address by direct match: " . substr($tx['address'], 0, 20) . "...");
                    $repository->processWalletTransaction($wallet, $tx, $targetAddress->id);
                    $walletsToUpdate[$wallet->id] = $wallet;
                    $processedCount++;
                    continue;
                }
                
                // CASE 2: Transaction FROM one of our tracked addresses (outgoing)
                // Check subaddr_index to see if sender is tracked
                if (isset($tx['subaddr_index'])) {
                    $minor = $tx['subaddr_index']['minor'] ?? null;
                    if ($minor !== null) {
                        $senderAddress = $activeAddresses->firstWhere('address_index', $minor);
                        if ($senderAddress) {
                            $wallet = $senderAddress->wallet;
                            Log::debug("  Matched {$tx['transfer_type']} tx from tracked address at subaddr_index {$minor}");
                            $repository->processWalletTransaction($wallet, $tx, $senderAddress->id);
                            $walletsToUpdate[$wallet->id] = $wallet;
                            $processedCount++;
                            // Don't continue - also check if ANY destination is tracked (internal transfer)
                        }
                    }
                }
                
                // CASE 3: Internal transfer - check if ANY destination address is tracked
                // This handles transfers from untracked addresses (like primary 0/0) to our tracked subaddresses
                if (isset($tx['destinations']) && $tx['transfer_type'] === 'out') {
                    foreach ($tx['destinations'] as $dest) {
                        if (isset($dest['address']) && isset($addressLookup[$dest['address']])) {
                            $receiverAddress = $addressLookup[$dest['address']];
                            $wallet = $receiverAddress->wallet;
                            
                            Log::info("  Internal transfer: Recording DEPOSIT to tracked address " . 
                                substr($dest['address'], 0, 20) . "... for " . ($dest['amount'] / 1e12) . " XMR");
                            
                            // Create a modified transaction object for the receiver's perspective
                            $receiverTx = $tx;
                            $receiverTx['address'] = $dest['address']; // Set as receiver's address
                            $receiverTx['amount'] = $dest['amount']; // Amount received
                            $receiverTx['transfer_type'] = 'in'; // Override to 'in' for deposit recording
                            
                            $repository->processWalletTransaction($wallet, $receiverTx, $receiverAddress->id);
                            $walletsToUpdate[$wallet->id] = $wallet;
                            $processedCount++;
                        }
                    }
                }
            }
            
            Log::debug("Processed {$processedCount} transaction(s) for " . count($walletsToUpdate) . " wallet(s)");

            // Update sync tracking for ALL addresses to current height
            // This prevents re-processing same blocks on next sync
            foreach ($activeAddresses as $addressRecord) {
                $addressRecord->update([
                    'last_synced_height' => $currentHeight,
                    'last_synced_at' => now(),
                ]);
            }

            // Update wallet balances for affected wallets
            foreach ($walletsToUpdate as $wallet) {
                $wallet->updateBalance();
            }

            Log::debug("=== Monero wallet sync completed (height: {$currentHeight}, 1 RPC call) ===");

        } catch (\Exception $e) {
            Log::error("Failed to sync Monero wallets: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync transactions for specific wallet.
     * @deprecated Use syncAllWallets() instead - all wallets now use same master wallet
     */
    public static function syncWalletTransactions(XmrWallet $wallet): void
    {
        try {
            Log::debug("  Wallet {$wallet->id}: Starting sync (subaddress mode)");

            $repository = new static();

            // Master wallet should already be open
            // Get all transfers and filter for this wallet's address
            $allTransfers = $repository->getAllTransfers();
            
            $walletAddress = $wallet->primary_address;
            $transfers = array_filter($allTransfers, function($tx) use ($walletAddress) {
                return isset($tx['address']) && $tx['address'] === $walletAddress;
            });

            Log::debug("  Found " . count($transfers) . " transfer(s) for wallet {$wallet->id}");

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
     * 
     * @param XmrWallet $wallet The wallet to process transaction for
     * @param array $txData Transaction data from RPC
     * @param int|null $xmrAddressId Optional pre-identified address ID (from incoming_transfers query)
     */
    private function processWalletTransaction(XmrWallet $wallet, array $txData, ?int $xmrAddressId = null): void
    {
        $txHash = $txData['txid'] ?? null;

        if (!$txHash) {
            Log::warning("Transaction missing txid", $txData);
            return;
        }

        // Determine transaction type FIRST (needed for duplicate check)
        // Monero RPC returns transfer_type as: 'in', 'out', 'pending', 'failed', 'pool'
        $type = match ($txData['transfer_type']) {
            'in' => 'deposit',
            'out' => 'withdrawal',
            'pending' => isset($txData['type']) && $txData['type'] === 'out' ? 'withdrawal' : 'deposit',
            'pool' => isset($txData['type']) && $txData['type'] === 'out' ? 'withdrawal' : 'deposit',
            default => null,
        };

        if (!$type) {
            Log::debug("Skipping unsupported transaction type: " . ($txData['transfer_type'] ?? 'unknown'));
            Log::debug("Transaction data: " . json_encode($txData));
            return;
        }

        // Check if transaction already exists FOR THIS WALLET with this TYPE
        // Note: Same txid can exist for multiple wallets (e.g., internal transfers between subaddresses)
        // The unique constraint is on (txid, xmr_wallet_id, type)
        // The duplicate prevention for wallet_transactions happens in XmrTransaction::processConfirmation()
        $existingTx = XmrTransaction::where('txid', $txHash)
            ->where('xmr_wallet_id', $wallet->id)
            ->where('type', $type)
            ->first();

        if ($existingTx) {
            Log::debug("Transaction {$txHash} already exists for wallet {$wallet->id}, updating confirmations");
            $this->updateExistingTransaction($existingTx, $txData);
            return;
        }

        // Convert from atomic units to XMR
        // For outgoing transfers, amount is in 'destinations' array
        $amount = ($txData['amount'] ?? 0) / 1e12;
        if ($amount == 0 && isset($txData['destinations'][0]['amount'])) {
            $amount = $txData['destinations'][0]['amount'] / 1e12;
        }
        $fee = ($txData['fee'] ?? 0) / 1e12;

        // Use provided address ID or find it from transaction data
        $xmrAddress = null;
        if ($xmrAddressId) {
            // Address ID provided from incoming_transfers query
            $xmrAddress = XmrAddress::find($xmrAddressId);
        } elseif (isset($txData['address'])) {
            // Fallback: find by address string
            $xmrAddress = $wallet->addresses()->where('address', $txData['address'])->first();
            if ($xmrAddress) {
                $xmrAddressId = $xmrAddress->id;
            }
        }

        // Mark address as used when first transaction is detected
        if ($xmrAddress && !$xmrAddress->is_used && $type === 'deposit') {
            $xmrAddress->markAsUsed();
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

        Log::debug("New XMR transaction detected: {$txHash} ({$type}) for {$amount} XMR");

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

        // CRITICAL: Prevent downgrading force-confirmed transactions (testing mode)
        if (config('monero.force_confirmations')) {
            $statusPriority = ['pending' => 1, 'confirmed' => 2, 'unlocked' => 3];
            $currentPriority = $statusPriority[$transaction->status] ?? 0;
            
            // Calculate what status blockchain would assign
            $blockchainStatus = 'pending';
            if ($newConfirmations > 0 && $newConfirmations < $minConfirmations) {
                $blockchainStatus = 'confirmed';
            } elseif ($newConfirmations >= $minConfirmations) {
                $blockchainStatus = 'unlocked';
            }
            $blockchainPriority = $statusPriority[$blockchainStatus] ?? 0;
            
            // Never downgrade when force confirmations enabled
            if ($blockchainPriority < $currentPriority) {
                Log::debug("          Force confirmation mode: Preventing downgrade from {$transaction->status} to {$blockchainStatus} (confirmations: {$newConfirmations})");
                return;
            }
        }

        // Stop updating once transaction has reached unlock threshold
        if ($oldConfirmations >= $minConfirmations) {
            Log::debug("          Transaction already has {$oldConfirmations} confirmations (>= {$minConfirmations} required), skipping update");
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

        Log::debug("Updated XMR transaction {$transaction->txid}: {$newConfirmations} confirmations");
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
