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

    public function __construct(public readonly int $walletId)
    {
        $this->onQueue('wallet-sync');
    }

    public function handle(): void
    {
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
