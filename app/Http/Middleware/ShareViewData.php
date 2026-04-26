<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\AppNotification;
use App\Models\ProductCategory;
use App\Models\ExchangeRate;
use Symfony\Component\HttpFoundation\Response;

class ShareViewData
{
    /**
     * Handle an incoming request.
     * Share common data with all views that need it (authenticated pages).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Load exchange rates (cached for 50 seconds - cron updates every minute)
        $btcRate = cache()->remember('exchange_rate_btc', 50, function () {
            return ExchangeRate::where('crypto_shortname', 'btc')->first();
        });
        
        $xmrRate = cache()->remember('exchange_rate_xmr', 50, function () {
            return ExchangeRate::where('crypto_shortname', 'xmr')->first();
        });

        // Load active categories (cached for 1 hour)
        $productCategories = cache()->remember('product_categories_with_counts', 3600, function () {
            return ProductCategory::where('product_categories.is_active', true)
                ->withCount(['listings' => function ($query) {
                    $query->where('listings.is_active', true);
                }])
                ->with(['products' => function ($query) {
                    $query->where('products.is_active', true)
                        ->withCount(['listings' => function ($q) {
                            $q->where('listings.is_active', true);
                        }]);
                }])
                ->get();
        });

        // Initialize default values for guest users
        $balance = [
            'btc' => ['balance' => 0, 'usd_value' => 0],
            'xmr' => ['balance' => 0, 'unlocked_balance' => 0, 'usd_value' => 0]
        ];
        
        $navigation_links = [
            'Home'      => route('home'),
            'Autoshop'  => route('autoshop.fullz.index'),
            'Orders'    => route('orders.index'),
            'Messages'  => route('messages.index'),
            'Wallets'   => route('wallet.index'),
            'Disputes'  => route('disputes.index'),
            'Forums'    => route('forum.index'),
            'Tickets'   => route('support.index'),
            'Profile'   => route('profile.show'),
        ];

        $unread_notification_count = 0;

        // Load user-specific data if authenticated
        if (auth()->check()) {
            $user = auth()->user();

            // Eager load wallet relationships to avoid N+1 queries
            $user->load(['btcWallet', 'xmrWallet']);

            // Get actual balance
            $balance = $user->getBalance();

            // Unread notification count
            $unread_notification_count = AppNotification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            // Role-specific links
            if ($user->hasRole('user')) {
                $navigation_links['Start Selling'] = route('vendor.convert');
            }

            if ($user->hasRole('vendor')) {
                $navigation_links['Vendor Dashboard'] = route('vendor.dashboard');
            }
        }

        // Share with all views
        View::share('productCategories', $productCategories);
        View::share('user_balance', $balance);
        View::share('navigation_links', $navigation_links);
        View::share('btcRate', $btcRate);
        View::share('xmrRate', $xmrRate);
        View::share('unread_notification_count', $unread_notification_count);

        return $next($request);
    }
}
