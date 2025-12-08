<?php

namespace App\Jobs;

use App\Repositories\BitcoinRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncBitcoinWallets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300;
    
    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public int $uniqueFor = 300;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }
    
    /**
     * Get the unique ID for the job to prevent concurrent execution.
     */
    public function uniqueId(): string
    {
        return 'bitcoin-wallet-sync';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug("SyncBitcoinWallets job started at " . now()->toDateTimeString());
        
        // Use cache lock to prevent concurrent execution
        $lock = Cache::lock('bitcoin:sync:lock', 300);

        if (!$lock->get()) {
            Log::warning('Bitcoin sync already running, skipping');
            return;
        }

        try {
            Log::info('Starting Bitcoin wallet synchronization');
            
            $startTime = microtime(true);
            BitcoinRepository::syncAllWallets();
            $duration = round(microtime(true) - $startTime, 2);
            
            Log::info("Bitcoin wallet synchronization completed successfully in {$duration} seconds");
            Log::debug("SyncBitcoinWallets job finished at " . now()->toDateTimeString());
        } catch (\Exception $e) {
            Log::error('Bitcoin wallet synchronization failed: ' . $e->getMessage());
            Log::debug("Stack trace: " . $e->getTraceAsString());
            throw $e;
        } finally {
            $lock->release();
            Log::debug("Cache lock released");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bitcoin wallet sync job failed: ' . $exception->getMessage());
    }
}
