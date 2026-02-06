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
         * Share data with authenticated views via middleware.
         * This runs only once per request via ShareViewData middleware.
         */
    }
}
