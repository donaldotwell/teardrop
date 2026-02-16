<?php

namespace App\Console\Commands;

use App\Jobs\SyncMoneroWallets as SyncMoneroWalletsJob;
use App\Repositories\MoneroRepository;
use Illuminate\Console\Command;

class SyncMoneroWallets extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'monero:sync
                            {--queue : Run sync via queue}
                            {--force : Force sync even if disabled}';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize Monero wallets with blockchain';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!config('monero.sync_enabled') && !$this->option('force')) {
            $this->warn('Monero sync is disabled in configuration');
            return self::FAILURE;
        }

        $this->info('Starting Monero wallet synchronization...');
        \Log::debug("monero:sync command executed by user/cron at " . now()->toDateTimeString());

        if ($this->option('queue')) {
            SyncMoneroWalletsJob::dispatch();
            $this->info('Monero sync job queued successfully');
            \Log::debug("Sync job dispatched to queue");
            return self::SUCCESS;
        }

        try {
            // Check RPC availability first
            $repository = new MoneroRepository();
            if (!$repository->isRpcAvailable()) {
                $this->error('Monero RPC service is not available');
                \Log::error('monero:sync failed - RPC service unavailable');
                return self::FAILURE;
            }

            $this->info('Running sync directly (not queued)...');
            \Log::debug("Running direct sync from console command");

            $startTime = microtime(true);

            // Sync user wallets
            $skipDays = config('monero.sync_idle_skip_days', 30);
            $userWallets = \App\Models\XmrWallet::where('is_active', true)
                ->where(function ($query) use ($skipDays) {
                    $query->whereNull('last_synced_at')
                          ->orWhere('last_synced_at', '>=', now()->subDays($skipDays));
                })
                ->get();

            $this->info("Found {$userWallets->count()} active user wallets to sync");
            $progressBar = $this->output->createProgressBar($userWallets->count());
            $progressBar->start();

            $syncedCount = 0;
            $errorCount = 0;

            foreach ($userWallets as $wallet) {
                try {
                    $repository->syncWallet($wallet);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->newLine();
                    $this->warn("Failed to sync wallet {$wallet->name}: {$e->getMessage()}");
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            // Sync escrow wallets
            $escrowWallets = \App\Models\EscrowWallet::where('currency', 'xmr')
                ->where('status', 'active')
                ->get();

            $this->info("Syncing {$escrowWallets->count()} active escrow wallets...");

            foreach ($escrowWallets as $escrowWallet) {
                try {
                    $repository->syncEscrowWallet($escrowWallet);
                } catch (\Exception $e) {
                    $this->warn("Failed to sync escrow wallet {$escrowWallet->wallet_name}: {$e->getMessage()}");
                }
            }

            $duration = round(microtime(true) - $startTime, 2);
            $this->info("Monero wallet synchronization completed in {$duration}s ({$syncedCount} user wallets synced, {$errorCount} errors, {$escrowWallets->count()} escrow wallets)");
            \Log::debug("Console command completed in {$duration}s");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Monero sync failed: ' . $e->getMessage());
            \Log::error("Console command failed: " . $e->getMessage());
            \Log::debug("Stack trace: " . $e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
