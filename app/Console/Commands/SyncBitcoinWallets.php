<?php

namespace App\Console\Commands;

use App\Jobs\SyncBitcoinWallets as SyncBitcoinWalletsJob;
use App\Repositories\BitcoinRepository;
use Illuminate\Console\Command;

class SyncBitcoinWallets extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bitcoin:sync
                            {--queue : Run sync via queue}
                            {--force : Force sync even if disabled}';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize Bitcoin wallets with blockchain';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!config('bitcoinrpc.sync_enabled') && !$this->option('force')) {
            $this->warn('Bitcoin sync is disabled in configuration');
            return self::FAILURE;
        }

        $this->info('Starting Bitcoin wallet synchronization...');
        \Log::debug("bitcoin:sync command executed by user/cron at " . now()->toDateTimeString());

        if ($this->option('queue')) {
            SyncBitcoinWalletsJob::dispatch();
            $this->info('Bitcoin sync job queued successfully');
            \Log::debug("Sync job dispatched to queue");
            return self::SUCCESS;
        }

        try {
            $this->info('Running sync directly (not queued)...');
            \Log::debug("Running direct sync from console command");
            
            $progressBar = $this->output->createProgressBar();
            $progressBar->start();

            $startTime = microtime(true);
            BitcoinRepository::syncAllWallets();
            $duration = round(microtime(true) - $startTime, 2);

            $progressBar->finish();
            $this->newLine();
            $this->info("Bitcoin wallet synchronization completed successfully in {$duration} seconds");
            \Log::debug("Console command completed successfully in {$duration}s");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Bitcoin sync failed: ' . $e->getMessage());
            \Log::error("Console command failed: " . $e->getMessage());
            \Log::debug("Stack trace: " . $e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
