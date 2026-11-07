<?php

namespace App\Jobs;

use App\Models\BtcWallet;
use App\Repositories\BitcoinRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSingleBtcWallet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public int $backoff = 10;

    // A sync job older than two dispatch cycles is superseded by a newer one;
    // running it only burns RPC time (and, after a worker outage, a backlog
    // of them would run for hours).
    public const MAX_AGE_SECONDS = 1200;

    public int $dispatchedAt;

    public function __construct(public readonly int $walletId)
    {
        $this->dispatchedAt = time();
        $this->onQueue('wallet-sync');
    }

    public function handle(): void
    {
        if (time() - $this->dispatchedAt > self::MAX_AGE_SECONDS) {
            return;
        }

        $wallet = BtcWallet::find($this->walletId);

        if (!$wallet || !$wallet->is_active) {
            return;
        }

        BitcoinRepository::syncWalletTransactions($wallet);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SyncSingleBtcWallet: wallet {$this->walletId} permanently failed: {$exception->getMessage()}");
    }
}
