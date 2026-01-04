<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Schedule::command('bitcoin:sync')
    ->everyMinute()
    ->withoutOverlapping()
    ->onFailure(function () {
        Log::error('Bitcoin sync command failed');
    });

Schedule::command('monero:sync')
    ->everyMinute()
    ->withoutOverlapping()
    ->onFailure(function () {
        Log::error('Monero sync command failed');
    });

Schedule::command('exchange:update')
    ->hourly()
    ->withoutOverlapping()
    ->onFailure(function () {
        Log::error('Exchange update command failed');
    });
