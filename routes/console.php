<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Jobs\CheckExpiredDisputeWindows;
use App\Jobs\UpdateVendorEarlyFinalizationStats;


// Transaction sync — every minute, 5-minute overlap lock expiry so a crashed run
// doesn't block syncs for 24 hours (Laravel's default withoutOverlapping expiry).
Schedule::command('bitcoin:sync')
    ->everyFiveMinutes()
    ->withoutOverlapping(5)
    ->onFailure(function () {
        Log::error('Bitcoin sync command failed');
    });

Schedule::command('monero:sync')
    ->everyFiveMinutes()
    ->withoutOverlapping(5)
    ->onFailure(function () {
        Log::error('Monero sync command failed');
    });

// Balance sync — every 5 minutes as a lightweight reconciliation pass.
Schedule::command('bitcoin:sync-balances')
    ->everyFiveMinutes()
    ->withoutOverlapping(5)
    ->onFailure(function () {
        Log::error('Bitcoin balance sync command failed');
    });

Schedule::command('monero:sync-balances')
    ->everyFiveMinutes()
    ->withoutOverlapping(5)
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
