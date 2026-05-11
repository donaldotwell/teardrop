<?php

use App\Console\Commands\CleanupExpiredDepositOrders;
use App\Jobs\CheckExpiredDisputeWindows;
use App\Jobs\UpdateVendorEarlyFinalizationStats;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
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

// Cart cleanup — soft-delete expired active carts, prune old soft-deleted rows.
Schedule::call(function () {
    $expired = CartItem::where('expires_at', '<', now())->count();
    CartItem::where('expires_at', '<', now())->delete();

    // Force-delete rows already soft-deleted more than 90 days ago.
    CartItem::onlyTrashed()
        ->where('deleted_at', '<', now()->subDays(90))
        ->forceDelete();

    if ($expired > 0) {
        Log::info("Cart cleanup: soft-deleted {$expired} expired cart item(s).");
    }
})->daily()->name('cart-cleanup')->withoutOverlapping(5);

// Cancel direct-deposit orders whose 48-hour window expired with no payment received.
Schedule::command(CleanupExpiredDepositOrders::class)
    ->hourly()
    ->withoutOverlapping(5)
    ->onFailure(function () {
        Log::error('CleanupExpiredDepositOrders failed');
    });
