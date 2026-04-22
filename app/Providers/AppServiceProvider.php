<?php

namespace App\Providers;

use App\Models\ProductCategory;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // All traffic arrives from 127.0.0.1 (Tor hidden service), so we key by
        // session ID for guests and user ID for authenticated users — never by IP.

        // Baseline flood protection — applied globally to all web routes
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(120)->by(
                $request->user()?->id ?? $request->session()->getId()
            );
        });

        // Auth endpoints: login, register, recovery, bot-challenge verify
        RateLimiter::for('auth-attempts', function (Request $request) {
            $key = $request->session()->getId();

            // Also throttle per submitted credential to block targeted brute-force
            $credential = strtolower(trim(
                $request->input('username') ?? $request->input('email') ?? ''
            ));

            if ($credential !== '') {
                return [
                    Limit::perMinute(10)->by($key),
                    Limit::perMinute(5)->by('cred|' . $credential),
                ];
            }

            return Limit::perMinute(10)->by($key);
        });

        // Financial operations: crypto withdrawals
        RateLimiter::for('withdrawals', function (Request $request) {
            return Limit::perMinute(3)->by(
                $request->user()?->id ?? $request->session()->getId()
            );
        });

        // User-generated content: messages, orders, forum posts, disputes, support
        RateLimiter::for('writes', function (Request $request) {
            return Limit::perMinute(30)->by(
                $request->user()?->id ?? $request->session()->getId()
            );
        });
    }
}
