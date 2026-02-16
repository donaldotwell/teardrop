<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\XmrWallet;
use App\Models\XmrAddress;
use App\Models\XmrTransaction;
use App\Models\EscrowWallet;
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
                          {--sync : Sync all wallet balances and transactions from RPC}
                          {--verify : Verify DB balances match RPC}
                          {--user= : Only process specific user ID}
                          {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup and sync Monero wallets (per-wallet architecture)';

    private MoneroRepository $repository;
    private bool $dryRun = false;
    private array $stats = [
        'users_processed' => 0,
        'wallets_created' => 0,
        'wallets_synced' => 0,
        'escrow_wallets_synced' => 0,
        'discrepancies_found' => 0,
        'errors' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->repository = new MoneroRepository();
        $this->dryRun = $this->option('dry-run');

        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║     Monero Cleanup & Verification (Per-Wallet Mode)       ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        if ($this->dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Check RPC availability
        if (!$this->repository->isRpcAvailable()) {
            $this->error('Monero RPC is not available. Please start monero-wallet-rpc with --wallet-dir first.');
            return 1;
        }

        $this->info('Monero RPC is available');
        $this->newLine();

        // Get users to process
        $users = $this->getUsersToProcess();
        $this->info("Found {$users->count()} users to process");
        $this->newLine();

        // Step 1: Ensure all users have per-wallet files and DB records
        $this->step1EnsureWallets($users);

        // Step 2: Sync wallet balances and transactions from RPC (if requested)
        if ($this->option('sync')) {
            $this->step2SyncWallets($users);
            $this->step3SyncEscrowWallets();
        }

        // Step 3: Verify balances match RPC (if requested)
        if ($this->option('verify')) {
            $this->step4VerifyBalances($users);
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
     * Step 1: Ensure all users have per-wallet files on RPC and DB records.
     * Uses MoneroRepository::getOrCreateWalletForUser() which creates the wallet
     * file, XmrWallet record, and initial XmrAddress record atomically.
     */
    private function step1EnsureWallets($users)
    {
        $this->info('--- Step 1: Checking Users Have XMR Wallets ---');

        $usersWithoutWallets = $users->filter(fn($user) => !$user->xmrWallet);

        if ($usersWithoutWallets->isEmpty()) {
            $this->info('  All users have XMR wallets');
            $this->newLine();
            return;
        }

        $this->warn("  Found {$usersWithoutWallets->count()} users without XMR wallets");

        $bar = $this->output->createProgressBar($usersWithoutWallets->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        foreach ($usersWithoutWallets as $user) {
            $bar->setMessage("Creating wallet for user {$user->id} ({$user->username_pub})");
            $bar->advance();

            if (!$this->dryRun) {
                try {
                    MoneroRepository::getOrCreateWalletForUser($user);
                    $this->stats['wallets_created']++;
                } catch (\Exception $e) {
                    $this->stats['errors']++;
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
        $this->info("  Created {$this->stats['wallets_created']} wallets");
        $this->newLine();
    }

    /**
     * Step 2: Sync user wallets from RPC.
     * Opens each per-user wallet file, refreshes, pulls get_transfers,
     * and reconciles DB records via MoneroRepository::syncWallet().
     */
    private function step2SyncWallets($users)
    {
        $this->info('--- Step 2: Syncing User Wallets from RPC ---');

        // Reload users to pick up any wallets created in step 1
        $wallets = XmrWallet::where('is_active', true)
            ->whereNotNull('user_id')
            ->when($this->option('user'), function ($query) {
                $query->where('user_id', $this->option('user'));
            })
            ->get();

        if ($wallets->isEmpty()) {
            $this->warn('  No active user wallets found to sync');
            $this->newLine();
            return;
        }

        $bar = $this->output->createProgressBar($wallets->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        foreach ($wallets as $wallet) {
            $bar->setMessage("Syncing wallet {$wallet->name}");
            $bar->advance();

            if (!$this->dryRun) {
                try {
                    $this->repository->syncWallet($wallet);
                    $this->stats['wallets_synced']++;
                } catch (\Exception $e) {
                    $this->stats['errors']++;
                    Log::error("Failed to sync wallet {$wallet->name}", [
                        'user_id' => $wallet->user_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                $this->stats['wallets_synced']++;
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("  Synced {$this->stats['wallets_synced']} user wallets");
        $this->newLine();
    }

    /**
     * Step 3: Sync active escrow wallets from RPC.
     */
    private function step3SyncEscrowWallets()
    {
        $this->info('--- Step 3: Syncing Escrow Wallets from RPC ---');

        $escrowWallets = EscrowWallet::where('currency', 'xmr')
            ->where('status', 'active')
            ->get();

        if ($escrowWallets->isEmpty()) {
            $this->info('  No active XMR escrow wallets to sync');
            $this->newLine();
            return;
        }

        $bar = $this->output->createProgressBar($escrowWallets->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        foreach ($escrowWallets as $escrowWallet) {
            $bar->setMessage("Syncing escrow {$escrowWallet->wallet_name}");
            $bar->advance();

            if (!$this->dryRun) {
                try {
                    $this->repository->syncEscrowWallet($escrowWallet);
                    $this->stats['escrow_wallets_synced']++;
                } catch (\Exception $e) {
                    $this->stats['errors']++;
                    Log::error("Failed to sync escrow wallet {$escrowWallet->wallet_name}", [
                        'order_id' => $escrowWallet->order_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                $this->stats['escrow_wallets_synced']++;
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("  Synced {$this->stats['escrow_wallets_synced']} escrow wallets");
        $this->newLine();
    }

    /**
     * Step 4: Verify DB balances match RPC on-chain balances.
     * Opens each wallet, calls get_balance, compares to DB columns.
     */
    private function step4VerifyBalances($users)
    {
        $this->info('--- Step 4: Verifying Balances ---');

        $wallets = XmrWallet::where('is_active', true)
            ->whereNotNull('user_id')
            ->when($this->option('user'), function ($query) {
                $query->where('user_id', $this->option('user'));
            })
            ->with('user')
            ->get();

        $table = [];

        $bar = $this->output->createProgressBar($wallets->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        foreach ($wallets as $wallet) {
            $this->stats['users_processed']++;

            $bar->setMessage("Verifying {$wallet->name}");
            $bar->advance();

            $dbBalance = $wallet->unlocked_balance;

            // Get RPC balance by opening the per-user wallet file
            try {
                $rpcBalance = $this->repository->getWalletBalance($wallet);
                $rpcUnlocked = $rpcBalance['unlocked_balance'];
            } catch (\Exception $e) {
                $this->stats['errors']++;
                Log::error("Failed to get RPC balance for wallet {$wallet->name}", [
                    'error' => $e->getMessage(),
                ]);
                $table[] = [
                    $wallet->user_id,
                    $wallet->user?->username_pub ?? 'N/A',
                    number_format($dbBalance, 12),
                    'ERROR',
                    'RPC FAILED',
                ];
                continue;
            }

            // Compare
            $diff = abs($dbBalance - $rpcUnlocked);
            $match = $diff < 0.000000000001; // Allow tiny floating point differences

            if (!$match) {
                $this->stats['discrepancies_found']++;
            }

            $table[] = [
                $wallet->user_id,
                $wallet->user?->username_pub ?? 'N/A',
                number_format($dbBalance, 12),
                number_format($rpcUnlocked, 12),
                $match ? 'OK' : 'MISMATCH',
            ];
        }

        $bar->finish();
        $this->newLine();

        if (!empty($table)) {
            $this->newLine();
            $this->table(
                ['User ID', 'Username', 'DB Balance (XMR)', 'RPC Balance (XMR)', 'Status'],
                $table
            );
        }

        if ($this->stats['discrepancies_found'] > 0) {
            $this->newLine();
            $this->error("  Found {$this->stats['discrepancies_found']} balance discrepancies!");
            $this->warn('  Run --sync to re-sync wallets from RPC.');
        } else {
            $this->newLine();
            $this->info('  All balances match!');
        }

        $this->newLine();
    }

    /**
     * Display summary of operations.
     */
    private function displaySummary()
    {
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║                         Summary                           ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->info("Users processed:         {$this->stats['users_processed']}");
        $this->info("Wallets created:         {$this->stats['wallets_created']}");
        $this->info("Wallets synced:          {$this->stats['wallets_synced']}");
        $this->info("Escrow wallets synced:   {$this->stats['escrow_wallets_synced']}");
        $this->info("Errors:                  {$this->stats['errors']}");

        if ($this->stats['discrepancies_found'] > 0) {
            $this->error("Discrepancies found:     {$this->stats['discrepancies_found']}");
        } else {
            $this->info("Discrepancies found:     0");
        }

        $this->newLine();

        if ($this->dryRun) {
            $this->warn('Dry run complete (no changes made)');
        } else {
            $this->info('Cleanup complete!');
        }
    }
}
