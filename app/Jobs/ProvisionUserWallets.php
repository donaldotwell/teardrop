<?php

namespace App\Jobs;

use App\Models\User;
use App\Repositories\BitcoinRepository;
use App\Repositories\MoneroRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionUserWallets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(public readonly int $userId)
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::warning("ProvisionUserWallets: user {$this->userId} not found");
            return;
        }

        try {
            BitcoinRepository::getOrCreateWalletForUser($user);
            Log::info("ProvisionUserWallets: BTC wallet provisioned for user {$user->id}");
        } catch (\Exception $e) {
            Log::error("ProvisionUserWallets: BTC provisioning failed for user {$user->id}: {$e->getMessage()}");
            throw $e;
        }

        try {
            MoneroRepository::getOrCreateWalletForUser($user);
            Log::info("ProvisionUserWallets: XMR wallet provisioned for user {$user->id}");
        } catch (\Exception $e) {
            Log::error("ProvisionUserWallets: XMR provisioning failed for user {$user->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProvisionUserWallets: permanently failed for user {$this->userId}: {$exception->getMessage()}");
    }
}
