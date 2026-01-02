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
     */
    public function handle(): void
    {
        Log::debug("SyncMoneroWallets job started at " . now()->toDateTimeString());

        // Use cache lock to prevent concurrent execution
        $lock = Cache::lock('monero:sync:lock', 300);

        if (!$lock->get()) {
            Log::warning('Monero sync already running, skipping');
            return;
        }

        try {
            // Check RPC availability first
            $repository = new MoneroRepository();
            if (!$repository->isRpcAvailable()) {
                Log::warning('Monero RPC service is not available, skipping sync');
                $lock->release();
                return;
            }

            Log::info('Starting Monero wallet synchronization');

            $startTime = microtime(true);

            // Sync user wallets
            MoneroRepository::syncAllWallets();

            // Sync escrow wallets
            $activeEscrowWallets = \App\Models\EscrowWallet::where('currency', 'xmr')
                ->where('status', 'active')
                ->get();

            Log::debug("Found {$activeEscrowWallets->count()} active Monero escrow wallets to sync");

            foreach ($activeEscrowWallets as $escrowWallet) {
                $xmrWallet = \App\Models\XmrWallet::where('name', $escrowWallet->wallet_name)->first();
                if ($xmrWallet) {
                    Log::debug("Syncing escrow wallet: {$escrowWallet->wallet_name}");
                    MoneroRepository::syncWalletTransactions($xmrWallet);

                    // Update escrow balance
                    $escrowWallet->updateBalance();

                    // Check if escrow is now funded
                    if (!$escrowWallet->order->escrow_funded_at && $escrowWallet->balance > 0) {
                        $escrowWallet->order->update([
                            'escrow_funded_at' => now(),
                        ]);
                        Log::info("Escrow wallet funded for order #{$escrowWallet->order_id}");
                    }
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            Log::info("Monero wallet synchronization completed successfully in {$duration} seconds");
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
