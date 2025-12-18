<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('bitcoin:sync')->everyMinute()->withoutOverlapping();
Schedule::command('monero:sync')->everyMinute()->withoutOverlapping();
Schedule::command('exchange:update')->hourly()->withoutOverlapping();
