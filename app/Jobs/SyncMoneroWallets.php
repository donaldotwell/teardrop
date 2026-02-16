<?php

namespace App\Jobs;

use App\Repositories\MoneroRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncMoneroWallets implements ShouldQueue
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
        return 'monero-wallet-sync';
    }

    /**
     * Execute the job.
     *
     * Iterates all active XmrWallet records and syncs each one individually
     * via the per-wallet architecture (open → refresh → get_transfers → close).
     * Then syncs active escrow wallets and checks for newly funded escrows.
     */
    public function handle(): void
    {
        Log::debug("SyncMoneroWallets job started at " . now()->toDateTimeString());

        // Use cache lock to prevent concurrent execution of sync jobs
        $lock = Cache::lock('monero:sync:lock', 300);

        if (!$lock->get()) {
            Log::warning('Monero sync already running, skipping');
            return;
        }

        try {
            $repository = new MoneroRepository();

            // Check RPC availability first
            if (!$repository->isRpcAvailable()) {
                Log::warning('Monero RPC service is not available, skipping sync');
                $lock->release();
                return;
            }

            Log::info('Starting Monero wallet synchronization');

            $startTime = microtime(true);

            // --- Sync user wallets ---
            $skipDays = config('monero.sync_idle_skip_days', 30);
            $userWallets = \App\Models\XmrWallet::where('is_active', true)
                ->where(function ($query) use ($skipDays) {
                    // Sync wallets that have never been synced, or were synced recently enough
                    // to still be considered active. Skip idle wallets to save RPC time.
                    $query->whereNull('last_synced_at')
                          ->orWhere('last_synced_at', '>=', now()->subDays($skipDays));
                })
                ->get();

            Log::debug("Found {$userWallets->count()} active user wallets to sync");

            $syncedCount = 0;
            $errorCount = 0;

            foreach ($userWallets as $wallet) {
                try {
                    $repository->syncWallet($wallet);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("Failed to sync XMR wallet {$wallet->name} (user #{$wallet->user_id}): {$e->getMessage()}");
                    // Continue with next wallet — don't let one failure stop the whole sync
                }
            }

            Log::info("User wallet sync: {$syncedCount} synced, {$errorCount} errors");

            // --- Sync escrow wallets ---
            $activeEscrowWallets = \App\Models\EscrowWallet::where('currency', 'xmr')
                ->where('status', 'active')
                ->get();

            Log::debug("Found {$activeEscrowWallets->count()} active Monero escrow wallets to sync");

            foreach ($activeEscrowWallets as $escrowWallet) {
                try {
                    $repository->syncEscrowWallet($escrowWallet);
                } catch (\Exception $e) {
                    Log::error("Failed to sync escrow wallet {$escrowWallet->wallet_name} (order #{$escrowWallet->order_id}): {$e->getMessage()}");
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            Log::info("Monero wallet synchronization completed in {$duration}s ({$syncedCount} user wallets, {$activeEscrowWallets->count()} escrow wallets)");
            Log::debug("SyncMoneroWallets job finished at " . now()->toDateTimeString());
        } catch (\Exception $e) {
            Log::error('Monero wallet synchronization failed: ' . $e->getMessage());
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
        Log::error('Monero wallet sync job failed: ' . $exception->getMessage());
    }
}
