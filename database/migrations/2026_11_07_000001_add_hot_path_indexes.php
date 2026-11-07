<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PostgreSQL does not index foreign-key columns automatically, so every
// per-listing / per-user lookup below was a sequential scan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['listing_id', 'status'], 'orders_listing_id_status_index'); // Listing::scopeInStock subquery
            $table->index('user_id', 'orders_user_id_index');
            $table->index('status', 'orders_status_index');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->index(['is_active', 'is_featured'], 'listings_is_active_is_featured_index');
            $table->index('product_id', 'listings_product_id_index');
            $table->index('user_id', 'listings_user_id_index');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('listing_id', 'reviews_listing_id_index');
            $table->index('user_id', 'reviews_user_id_index');
        });

        Schema::table('listing_media', function (Blueprint $table) {
            $table->index(['listing_id', 'order'], 'listing_media_listing_id_order_index');
        });

        Schema::table('btc_wallets', function (Blueprint $table) {
            $table->index('user_id', 'btc_wallets_user_id_index');
        });

        Schema::table('xmr_wallets', function (Blueprint $table) {
            $table->index('user_id', 'xmr_wallets_user_id_index');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->index(['user_id', 'expires_at'], 'cart_items_user_id_expires_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_listing_id_status_index');
            $table->dropIndex('orders_user_id_index');
            $table->dropIndex('orders_status_index');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex('listings_is_active_is_featured_index');
            $table->dropIndex('listings_product_id_index');
            $table->dropIndex('listings_user_id_index');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_listing_id_index');
            $table->dropIndex('reviews_user_id_index');
        });

        Schema::table('listing_media', function (Blueprint $table) {
            $table->dropIndex('listing_media_listing_id_order_index');
        });

        Schema::table('btc_wallets', function (Blueprint $table) {
            $table->dropIndex('btc_wallets_user_id_index');
        });

        Schema::table('xmr_wallets', function (Blueprint $table) {
            $table->dropIndex('xmr_wallets_user_id_index');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('cart_items_user_id_expires_at_index');
        });
    }
};
