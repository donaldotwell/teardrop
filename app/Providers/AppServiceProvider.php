<?php

namespace App\Providers;

use App\Models\ProductCategory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Share the product categories with the app layout.
         */
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $productCategories = ProductCategory::withCount('listings')
                    ->with(['products' => function ($query) {
                        $query->withCount('listings');
                    }])
                    ->get();

                $user = auth()->user();

                // navigation_links
                $navigation_links = [
                    'Home' => route('home'),
                    'Orders' => route('orders.index'),
                    'Messages' => route('messages.index'),
                    'Wallets' => route('wallet.index'),
                    'Profile' => route('profile.show'),
                    'Disputes' => route('disputes.index'),
                    'Tickets' => route('support.index'),
                    'Forums' => route('forum.index'),
                    'URL Verification' => '#',
                    'Harm Reduction' => '#'
                ];

                if ($user->hasRole('user')) {
                    $navigation_links['Start Selling'] = route('vendor.convert');
                }

                $view->with('productCategories', $productCategories)
                    ->with('user_balance', $user->getBalance())
                    ->with('navigation_links', $navigation_links);
            }
        });
    }
}
