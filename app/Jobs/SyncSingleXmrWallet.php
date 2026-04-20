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

    public function __construct(public readonly int $walletId)
    {
        $this->onQueue('wallet-sync-xmr');
    }

    public function handle(): void
    {
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
