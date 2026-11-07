<?php

namespace App\Jobs;

use App\Models\XmrWallet;
use App\Repositories\MoneroRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

// NOTE: monero-wallet-rpc can only have one wallet open at a time.
// The 'wallet-sync-xmr' queue MUST be processed by exactly ONE worker.
class SyncSingleXmrWallet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 15;

    // A sync job older than two dispatch cycles is superseded by a newer one;
    // running it only holds the global Monero RPC lock for nothing (and, after
    // a worker outage, a backlog of them would stall web requests for hours).
    public const MAX_AGE_SECONDS = 1200;

    public int $dispatchedAt;

    public function __construct(public readonly int $walletId)
    {
        $this->dispatchedAt = time();
        $this->onQueue('wallet-sync-xmr');
    }

    public function handle(): void
    {
        if (time() - $this->dispatchedAt > self::MAX_AGE_SECONDS) {
            return;
        }

        $wallet = XmrWallet::find($this->walletId);

        if (!$wallet || !$wallet->is_active) {
            return;
        }

        $repository = new MoneroRepository();
        $repository->syncWallet($wallet);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SyncSingleXmrWallet: wallet {$this->walletId} permanently failed: {$exception->getMessage()}");
    }
}
