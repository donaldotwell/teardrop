<?php

namespace App\Console\Commands;

use App\Models\BtcWallet;
use App\Repositories\BitcoinRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncBitcoinWalletBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitcoin:sync-balances
                            {--force : Force sync even if RPC check fails}
                            {--wallet= : Sync specific wallet ID only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update Bitcoin wallet balances from RPC';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bitcoin Wallet Balance Sync');
        $this->newLine();

        $repository = new BitcoinRepository();

        // Check RPC availability
        if (!$this->option('force')) {
            $this->info('Checking Bitcoin RPC availability...');
            
            if (!$repository->isRpcAvailable()) {
                $this->error('Bitcoin RPC service is not available. Use --force to skip this check.');
                Log::warning('Bitcoin balance sync skipped: RPC unavailable');
                return 1;
            }

            $this->info('✓ Bitcoin RPC is available');
        }

        $this->newLine();

        // Get wallets to sync
        $walletId = $this->option('wallet');

        if ($walletId) {
            $wallets = BtcWallet::where('id', $walletId)->where('is_active', true)->get();
            
            if ($wallets->isEmpty()) {
                $this->warn("No active wallet found with ID {$walletId}");
                return 1;
            }

            $this->info("Targeting specific wallet: ID {$walletId}");
        } else {
            $wallets = BtcWallet::where('is_active', true)->get();
            $this->info("Found {$wallets->count()} active Bitcoin wallets");
        }

        if ($wallets->isEmpty()) {
            $this->warn('No active Bitcoin wallets found.');
            return 0;
        }

        $this->newLine();

        $successCount = 0;
        $failureCount = 0;
        $skippedCount = 0;

        $progressBar = $this->output->createProgressBar($wallets->count());
        $progressBar->start();

        foreach ($wallets as $wallet) {
            try {
                // Validate wallet exists and has associated user
                if (!$wallet->user) {
                    $this->warn("Wallet {$wallet->id} has no associated user, skipping.");
                    $skippedCount++;
                    $progressBar->advance();
                    continue;
                }

                // Get RPC balance for this wallet
                $rpcBalance = $repository->getWalletBalance($wallet->name);

                // Update wallet balance in database
                $wallet->update([
                    'balance' => $rpcBalance,
                    'last_synced_at' => now(),
                ]);

                Log::debug("Bitcoin wallet balance synced", [
                    'wallet_id' => $wallet->id,
                    'user_id' => $wallet->user_id,
                    'balance' => $rpcBalance,
                ]);

                $successCount++;

            } catch (\Exception $e) {
                $this->warn("Failed to sync wallet {$wallet->id}: {$e->getMessage()}");
                Log::error("Bitcoin wallet balance sync failed", [
                    'wallet_id' => $wallet->id,
                    'error' => $e->getMessage(),
                ]);
                $failureCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Bitcoin balance sync completed:");
        $this->info("✓ Success: {$successCount} wallets");
        $this->info("✗ Failed: {$failureCount} wallets");
        $this->info("⊘ Skipped: {$skippedCount} wallets");

        if ($failureCount > 0) {
            $this->warn('Some wallets failed to sync. Check the logs for details.');
            return 1;
        }

        $this->info('All Bitcoin wallet balances have been synced.');
        return 0;
    }
}
