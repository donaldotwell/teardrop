<?php

namespace App\Http\Middleware;

use App\Models\BtcWallet;
use App\Models\XmrWallet;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class UpdateLastSeenAt
{
    /**
     * Handle an incoming request.
     *
     * Update the user's last_seen_at timestamp once per minute to avoid excessive database writes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $cacheKey = "user_last_seen_{$user->id}";

            // Only update if we haven't updated in the last minute (reduces DB writes)
            if (!Cache::has($cacheKey)) {
                $user->update(['last_seen_at' => now()]);

                // The 10-minute wallet sync only covers wallets active within
                // sync_active_window_hours; a logged-in user must count as active or
                // their deposits are never picked up. Query-builder updates so no
                // model events (and no balance-cache churn) fire.
                BtcWallet::where('user_id', $user->id)->update(['last_active_at' => now()]);
                XmrWallet::where('user_id', $user->id)->update(['last_active_at' => now()]);

                // Cache for 1 minute to prevent excessive updates
                Cache::put($cacheKey, true, 60);
            }
        }

        return $next($request);
    }
}
