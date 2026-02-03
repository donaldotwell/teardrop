<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\XmrWallet;
use App\Models\XmrAddress;
use App\Models\XmrTransaction;
use App\Repositories\MoneroRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MoneroCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monero:cleanup
                          {--sync-addresses : Sync all address balances from RPC}
                          {--rebuild-transactions : Rebuild transaction history from RPC (USE WITH CAUTION)}
                          {--verify : Verify balances match RPC}
                          {--user= : Only process specific user ID}
                          {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup and sync Monero wallets, addresses, and balances';

    private MoneroRepository $repository;
    private bool $dryRun = false;
    private array $stats = [
        'users_processed' => 0,
        'wallets_created' => 0,
        'addresses_created' => 0,
        'addresses_synced' => 0,
        'transactions_created' => 0,
        'discrepancies_found' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->repository = new MoneroRepository();
        $this->dryRun = $this->option('dry-run');

        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║          Monero System Cleanup & Verification             ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        if ($this->dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Check RPC availability
        if (!$this->repository->isRpcAvailable()) {
            $this->error('❌ Monero RPC is not available. Please start monero-wallet-rpc first.');
            return 1;
        }

        $this->info('✓ Monero RPC is available');
        $this->newLine();

        // Get users to process
        $users = $this->getUsersToProcess();
        $this->info("Found {$users->count()} users to process");
        $this->newLine();

        // Step 1: Ensure all users have XmrWallet records
        $this->step1EnsureWallets($users);

        // Step 2: Ensure all wallets have at least one address
        $this->step2EnsureAddresses($users);

        // Step 3: Sync address balances from RPC (if requested)
        if ($this->option('sync-addresses')) {
            $this->step3SyncAddressBalances($users);
        }

        // Step 4: Rebuild transactions from RPC (if requested)
        if ($this->option('rebuild-transactions')) {
            if ($this->confirm('⚠️  Rebuilding transactions will query RPC for ALL transfers. This may take a while. Continue?', false)) {
                $this->step4RebuildTransactions($users);
            }
        }

        // Step 5: Verify balances match RPC (if requested)
        if ($this->option('verify')) {
            $this->step5VerifyBalances($users);
        }

        // Display summary
        $this->displaySummary();

        return 0;
    }

    /**
     * Get users to process based on options.
     */
    private function getUsersToProcess()
    {
        if ($userId = $this->option('user')) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                exit(1);
            }
            return collect([$user]);
        }

        return User::where('status', 'active')->get();
    }

    /**
     * Step 1: Ensure all users have XmrWallet records.
     */
    private function step1EnsureWallets($users)
    {
        $this->info('━━━ Step 1: Checking Users Have XMR Wallets ━━━');

        $usersWithoutWallets = $users->filter(fn($user) => !$user->xmrWallet);

        if ($usersWithoutWallets->isEmpty()) {
            $this->info('  ✓ All users have XMR wallets');
            $this->newLine();
            return;
        }

        $this->warn("  ⚠ Found {$usersWithoutWallets->count()} users without XMR wallets");

        $bar = $this->output->createProgressBar($usersWithoutWallets->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        foreach ($usersWithoutWallets as $user) {
            $bar->setMessage("Creating wallet for user {$user->id} ({$user->username_pub})");
            $bar->advance();

            if (!$this->dryRun) {
                try {
                    XmrWallet::create([
                        'user_id' => $user->id,
                        'name' => config('monero.master_wallet_name', 'teardrop_master'),
                        'primary_address' => null, // Will be set when address is created
                        'balance' => 0,
                        'unlocked_balance' => 0,
                        'is_active' => true,
                    ]);

                    $this->stats['wallets_created']++;
                } catch (\Exception $e) {
                    Log::error("Failed to create XMR wallet for user {$user->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                $this->stats['wallets_created']++;
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Created {$this->stats['wallets_created']} wallets");
        $this->newLine();
    }

    /**
     * Step 2: Ensure all wallets have at least one address.
     */
    private function step2EnsureAddresses($users)
    {
        $this->info('━━━ Step 2: Checking Wallets Have Addresses ━━━');

        // Reload users to get newly created wallets
        $users = $users->fresh();

        $walletsNeedingAddresses = $users->map(fn($user) => $user->xmrWallet)
            ->filter()
            ->filter(fn($wallet) => $wallet->addresses()->count() === 0);

        if ($walletsNeedingAddresses->isEmpty()) {
            $this->info('  ✓ All wallets have addresses');
            $this->newLine();
            return;
        }

        $this->warn("  ⚠ Found {$walletsNeedingAddresses->count()} wallets without addresses");

        $bar = $this->output->createProgressBar($walletsNeedingAddresses->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        foreach ($walletsNeedingAddresses as $wallet) {
            $user = $wallet->user;
            $bar->setMessage("Creating address for {$user->username_pub}");
            $bar->advance();

            if (!$this->dryRun) {
                try {
                    // Create subaddress via RPC
                    $label = "User {$user->id} - {$user->username_pub}";
                    $subaddressData = MoneroRepository::createSubaddress(
                        config('monero.master_wallet_name', 'teardrop_master'),
                        0, // account_index
                        $label
                    );

                    if ($subaddressData && isset($subaddressData['address'])) {
                        // Create address record
                        $address = $wallet->addresses()->create([
                            'address' => $subaddressData['address'],
                            'account_index' => 0,
                            'address_index' => $subaddressData['address_index'],
                            'label' => $label,
                            'balance' => 0,
                            'is_used' => false,
                        ]);

                        // Update wallet primary_address if not set
                        if (!$wallet->primary_address) {
                            $wallet->update(['primary_address' => $subaddressData['address']]);
                        }

                        $this->stats['addresses_created']++;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to create address for wallet {$wallet->id}", [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                $this->stats['addresses_created']++;
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Created {$this->stats['addresses_created']} addresses");
        $this->newLine();
    }

    /**
     * Step 3: Sync address balances from RPC.
     */
    private function step3SyncAddressBalances($users)
    {
        $this->info('━━━ Step 3: Syncing Address Balances from RPC ━━━');

        // Get all addresses
        $addresses = XmrAddress::whereIn('xmr_wallet_id', 
            $users->map(fn($u) => $u->xmrWallet?->id)->filter()
        )->get();

        if ($addresses->isEmpty()) {
            $this->warn('  ⚠ No addresses found to sync');
            $this->newLine();
            return;
        }

        $bar = $this->output->createProgressBar($addresses->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        foreach ($addresses as $address) {
            $bar->setMessage("Address #{$address->address_index}");
            $bar->advance();

            if (!$this->dryRun) {
                try {
                    // Query RPC for address balance
                    $balanceData = $this->repository->rpcCall('get_balance', [
                        'account_index' => $address->account_index,
                        'address_indices' => [$address->address_index],
                    ]);

                    if (isset($balanceData['per_subaddress'][0])) {
                        $subaddress = $balanceData['per_subaddress'][0];

                        // Convert from atomic units to XMR
                        $balance = $subaddress['balance'] / 1e12;
                        $unlockedBalance = $subaddress['unlocked_balance'] / 1e12;

                        // Update address record
                        $address->update([
                            'balance' => $balance,
                            'total_received' => $subaddress['balance'] / 1e12, // Approximation
                            'is_used' => $balance > 0 || $subaddress['num_unspent_outputs'] > 0,
                        ]);

                        $this->stats['addresses_synced']++;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to sync address #{$address->address_index}", [
                        'address_id' => $address->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                $this->stats['addresses_synced']++;
            }

            // Small delay to avoid overwhelming RPC
            usleep(100000); // 0.1 second
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Synced {$this->stats['addresses_synced']} addresses");
        $this->newLine();
    }

    /**
     * Step 4: Rebuild transactions from RPC (CAREFUL - can be slow).
     */
    private function step4RebuildTransactions($users)
    {
        $this->info('━━━ Step 4: Rebuilding Transaction History ━━━');
        $this->warn('  ⚠ This may take several minutes...');
        $this->newLine();

        foreach ($users as $user) {
            if (!$user->xmrWallet) {
                continue;
            }

            $wallet = $user->xmrWallet;
            $addresses = $wallet->addresses;

            $this->info("  Processing user: {$user->username_pub} ({$addresses->count()} addresses)");

            foreach ($addresses as $address) {
                if (!$this->dryRun) {
                    try {
                        // ===== INCOMING TRANSFERS =====
                        $incomingTransfers = $this->repository->rpcCall('incoming_transfers', [
                            'transfer_type' => 'all',
                            'account_index' => $address->account_index,
                            'subaddr_indices' => [$address->address_index],
                        ]);

                        if (isset($incomingTransfers['transfers'])) {
                            foreach ($incomingTransfers['transfers'] as $transfer) {
                                // Check if transaction already exists
                                $exists = XmrTransaction::where('txid', $transfer['tx_hash'])
                                    ->where('xmr_wallet_id', $wallet->id)
                                    ->where('xmr_address_id', $address->id)
                                    ->exists();

                                if (!$exists) {
                                    XmrTransaction::create([
                                        'xmr_wallet_id' => $wallet->id,
                                        'xmr_address_id' => $address->id,
                                        'txid' => $transfer['tx_hash'],
                                        'type' => 'incoming',
                                        'amount' => $transfer['amount'] / 1e12,
                                        'confirmations' => $transfer['unlocked'] ? config('monero.min_confirmations', 10) : 0,
                                        'status' => $transfer['unlocked'] ? 'unlocked' : 'confirmed',
                                        'raw_transaction' => $transfer,
                                        'confirmed_at' => $transfer['unlocked'] ? now() : null,
                                        'unlocked_at' => $transfer['unlocked'] ? now() : null,
                                    ]);

                                    $this->stats['transactions_created']++;
                                }
                            }
                        }

                        // ===== OUTGOING TRANSFERS =====
                        $outgoingTransfers = $this->repository->rpcCall('get_transfers', [
                            'out' => true,
                            'pending' => true,
                            'failed' => true,
                            'account_index' => $address->account_index,
                            'subaddr_indices' => [$address->address_index],
                        ]);

                        // Process outgoing transfers
                        foreach (['out', 'pending', 'failed'] as $type) {
                            if (isset($outgoingTransfers[$type])) {
                                foreach ($outgoingTransfers[$type] as $transfer) {
                                    // Check if transaction already exists
                                    $exists = XmrTransaction::where('txid', $transfer['txid'])
                                        ->where('xmr_wallet_id', $wallet->id)
                                        ->exists();

                                    if (!$exists) {
                                        // Determine transaction type based on destination or memo
                                        $txType = 'withdrawal'; // Default
                                        $status = $type === 'failed' ? 'failed' : 'confirmed';
                                        
                                        if ($type === 'pending') {
                                            $status = 'pending';
                                        }

                                        XmrTransaction::create([
                                            'xmr_wallet_id' => $wallet->id,
                                            'xmr_address_id' => $address->id,
                                            'txid' => $transfer['txid'],
                                            'type' => $txType,
                                            'amount' => -abs($transfer['amount'] / 1e12), // Negative for outgoing
                                            'fee' => isset($transfer['fee']) ? $transfer['fee'] / 1e12 : 0,
                                            'confirmations' => $transfer['confirmations'] ?? 0,
                                            'status' => $status,
                                            'raw_transaction' => $transfer,
                                            'confirmed_at' => isset($transfer['timestamp']) ? date('Y-m-d H:i:s', $transfer['timestamp']) : now(),
                                            'unlocked_at' => ($transfer['confirmations'] ?? 0) >= config('monero.min_confirmations', 10) ? now() : null,
                                        ]);

                                        $this->stats['transactions_created']++;
                                    }
                                }
                            }
                        }

                    } catch (\Exception $e) {
                        Log::error("Failed to rebuild transactions for address {$address->id}", [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Delay to avoid overwhelming RPC
                usleep(200000); // 0.2 seconds
            }

            $this->info("    ✓ Processed {$user->username_pub}");
        }

        $this->newLine();
        $this->info("  ✓ Created {$this->stats['transactions_created']} transaction records");
        $this->newLine();
    }

    /**
     * Step 5: Verify balances match between DB and RPC.
     */
    private function step5VerifyBalances($users)
    {
        $this->info('━━━ Step 5: Verifying Balances ━━━');

        $table = [];

        foreach ($users as $user) {
            $this->stats['users_processed']++;

            if (!$user->xmrWallet) {
                continue;
            }

            $wallet = $user->xmrWallet;

            // Calculate balance from XmrTransaction records using the same logic as User::getXmrBalance()
            $totalIncoming = XmrTransaction::where('xmr_wallet_id', $wallet->id)
                ->where('type', 'incoming')
                ->where('status', 'unlocked')
                ->sum('amount');
            
            $totalOutgoing = XmrTransaction::where('xmr_wallet_id', $wallet->id)
                ->whereIn('type', ['withdrawal', 'escrow_funding', 'direct_payment', 'feature_payment', 'refund'])
                ->where('status', '!=', 'failed')
                ->sum('amount');
            
            $dbBalance = $totalIncoming - abs($totalOutgoing);

            // Get RPC balance (sum all addresses)
            $rpcBalance = 0;
            try {
                foreach ($wallet->addresses as $address) {
                    $balanceData = $this->repository->rpcCall('get_balance', [
                        'account_index' => $address->account_index,
                        'address_indices' => [$address->address_index],
                    ]);

                    if (isset($balanceData['per_subaddress'][0])) {
                        $rpcBalance += $balanceData['per_subaddress'][0]['unlocked_balance'] / 1e12;
                    }

                    usleep(100000); // 0.1 second delay
                }
            } catch (\Exception $e) {
                Log::error("Failed to get RPC balance for wallet {$wallet->id}", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            // Compare
            $diff = abs($dbBalance - $rpcBalance);
            $match = $diff < 0.000000000001; // Allow tiny floating point differences

            if (!$match) {
                $this->stats['discrepancies_found']++;
            }

            $table[] = [
                $user->id,
                $user->username_pub,
                number_format($dbBalance, 12),
                number_format($rpcBalance, 12),
                $match ? '✓' : '✗ MISMATCH',
            ];
        }

        if (!empty($table)) {
            $this->newLine();
            $this->table(
                ['User ID', 'Username', 'DB Balance (XMR)', 'RPC Balance (XMR)', 'Status'],
                $table
            );
        }

        if ($this->stats['discrepancies_found'] > 0) {
            $this->newLine();
            $this->error("  ✗ Found {$this->stats['discrepancies_found']} balance discrepancies!");
            $this->warn('  Run --rebuild-transactions to fix missing transaction records.');
        } else {
            $this->newLine();
            $this->info('  ✓ All balances match!');
        }

        $this->newLine();
    }

    /**
     * Display summary of operations.
     */
    private function displaySummary()
    {
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║                         Summary                            ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->info("Users processed:       {$this->stats['users_processed']}");
        $this->info("Wallets created:       {$this->stats['wallets_created']}");
        $this->info("Addresses created:     {$this->stats['addresses_created']}");
        $this->info("Addresses synced:      {$this->stats['addresses_synced']}");
        $this->info("Transactions created:  {$this->stats['transactions_created']}");

        if ($this->stats['discrepancies_found'] > 0) {
            $this->error("Discrepancies found:   {$this->stats['discrepancies_found']}");
        } else {
            $this->info("Discrepancies found:   0");
        }

        $this->newLine();

        if ($this->dryRun) {
            $this->warn('✓ Dry run complete (no changes made)');
        } else {
            $this->info('✓ Cleanup complete!');
        }
    }
}
