<?php

use App\Jobs\CheckExpiredDisputeWindows;
use App\Jobs\UpdateVendorEarlyFinalizationStats;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Transaction sync — dispatches SyncBitcoinWallets job which fans out one job per wallet.
// 10-minute interval gives Bitcoin Core breathing room between RPC bursts.
Schedule::command('bitcoin:sync --queue')
    ->everyTenMinutes()
    ->withoutOverlapping(10)
    ->onFailure(function () {
        Log::error('Bitcoin sync command failed');
    });

Schedule::command('monero:sync --queue')
    ->everyTenMinutes()
    ->withoutOverlapping(10)
    ->onFailure(function () {
        Log::error('Monero sync command failed');
    });

// Balance reconciliation — hourly is sufficient; the transaction sync keeps balances current.
Schedule::command('bitcoin:sync-balances')
    ->hourly()
    ->withoutOverlapping(10)
    ->onFailure(function () {
        Log::error('Bitcoin balance sync command failed');
    });

Schedule::command('monero:sync-balances')
    ->hourly()
    ->withoutOverlapping(10)
    ->onFailure(function () {
        Log::error('Monero balance sync command failed');
    });

Schedule::command('exchange:update')
    ->hourly()
    ->withoutOverlapping(10)
    ->onFailure(function () {
        Log::error('Exchange update command failed');
    });

Schedule::command('ratings:aggregate')
    ->everyThirtyMinutes()
    ->withoutOverlapping(10)
    ->onFailure(function () {
        Log::error('Ratings aggregation command failed');
    });

Schedule::job(new CheckExpiredDisputeWindows)
    ->hourly()
    ->withoutOverlapping(10)
    ->onFailure(function () {
        Log::error('Check expired dispute windows job failed');
    });

Schedule::job(new UpdateVendorEarlyFinalizationStats)
    ->daily()
    ->withoutOverlapping(30)
    ->onFailure(function () {
        Log::error('Update vendor early finalization stats job failed');
    });
