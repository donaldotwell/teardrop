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

            $progressBar = $this->output->createProgressBar();
            $progressBar->start();

            $startTime = microtime(true);
            MoneroRepository::syncAllWallets();
            $duration = round(microtime(true) - $startTime, 2);

            $progressBar->finish();
            $this->newLine();
            $this->info("Monero wallet synchronization completed successfully in {$duration} seconds");
            \Log::debug("Console command completed successfully in {$duration}s");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Monero sync failed: ' . $e->getMessage());
            \Log::error("Console command failed: " . $e->getMessage());
            \Log::debug("Stack trace: " . $e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
