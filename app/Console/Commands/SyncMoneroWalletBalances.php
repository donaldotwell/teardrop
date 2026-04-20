<?php

namespace App\Console\Commands;

use App\Models\XmrWallet;
use App\Repositories\MoneroRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncMoneroWalletBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monero:sync-balances
                            {--force : Force sync even if RPC check fails}
                            {--wallet= : Sync specific wallet ID only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update Monero wallet balances from RPC';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Monero Wallet Balance Sync');
        $this->newLine();

        $repository = new MoneroRepository();

        // Check RPC availability
        if (!$this->option('force')) {
            $this->info('Checking Monero RPC availability...');
            
            if (!$repository->isRpcAvailable()) {
                $this->error('Monero RPC service is not available. Use --force to skip this check.');
                Log::warning('Monero balance sync skipped: RPC unavailable');
                return 1;
            }

            $this->info('✓ Monero RPC is available');
        }

        $this->newLine();

        // Get wallets to sync
        $walletId = $this->option('wallet');

        if ($walletId) {
            $total = XmrWallet::where('id', $walletId)->where('is_active', true)->count();

            if ($total === 0) {
                $this->warn("No active wallet found with ID {$walletId}");
                return 1;
            }

            $this->info("Targeting specific wallet: ID {$walletId}");
        } else {
            $total = XmrWallet::where('is_active', true)->count();
            $this->info("Found {$total} active Monero wallets");
        }

        if ($total === 0) {
            $this->warn('No active Monero wallets found.');
            return 0;
        }

        $this->newLine();

        $successCount = 0;
        $failureCount = 0;
        $skippedCount = 0;

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $query = XmrWallet::where('is_active', true);
        if ($walletId) {
            $query->where('id', $walletId);
        }

        $query->chunkById(100, function ($wallets) use ($repository, $progressBar, &$successCount, &$failureCount, &$skippedCount) {
            foreach ($wallets as $wallet) {
                try {
                    if (!$wallet->user) {
                        $this->warn("Wallet {$wallet->id} has no associated user, skipping.");
                        $skippedCount++;
                        $progressBar->advance();
                        continue;
                    }

                    $balanceData = $repository->getWalletBalance($wallet);

                    $wallet->update([
                        'balance' => $balanceData['balance'],
                        'unlocked_balance' => $balanceData['unlocked_balance'],
                        'last_synced_at' => now(),
                    ]);

                    Log::debug("Monero wallet balance synced", [
                        'wallet_id' => $wallet->id,
                        'user_id' => $wallet->user_id,
                        'balance' => $balanceData['balance'],
                        'unlocked_balance' => $balanceData['unlocked_balance'],
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $this->warn("Failed to sync wallet {$wallet->id}: {$e->getMessage()}");
                    Log::error("Monero wallet balance sync failed", [
                        'wallet_id' => $wallet->id,
                        'error' => $e->getMessage(),
                    ]);
                    $failureCount++;
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Monero balance sync completed:");
        $this->info("✓ Success: {$successCount} wallets");
        $this->info("✗ Failed: {$failureCount} wallets");
        $this->info("⊘ Skipped: {$skippedCount} wallets");

        if ($failureCount > 0) {
            $this->warn('Some wallets failed to sync. Check the logs for details.');
            return 1;
        }

        $this->info('All Monero wallet balances have been synced.');
        return 0;
    }
}
