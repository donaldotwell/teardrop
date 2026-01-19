<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vendor Conversion Fee
    |--------------------------------------------------------------------------
    |
    | The amount in USD required to convert a regular user account to a vendor account.
    | This will be converted to BTC or XMR based on the currency selected.
    |
    */
    'vendor_conversion_usd' => env('VENDOR_CONVERSION_FEE_USD', 1000),

    /*
    |--------------------------------------------------------------------------
    | Order Completion Fee
    |--------------------------------------------------------------------------
    |
    | Percentage fee charged on completed orders. This fee is deducted from
    | the order total and sent to the admin wallet.
    |
    */
    'order_completion_percentage' => env('ORDER_COMPLETION_FEE_PERCENT', 3),

    /*
    |--------------------------------------------------------------------------
    | Admin Bitcoin Wallet
    |--------------------------------------------------------------------------
    |
    | The wallet name in btc_wallets table that receives all service fees.
    | This wallet must exist in the system.
    |
    */
    'admin_btc_wallet_name' => env('ADMIN_BTC_WALLET_NAME', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Admin Monero Wallet (Future)
    |--------------------------------------------------------------------------
    |
    | The wallet name for Monero admin fees. Not implemented yet.
    |
    */
    'admin_xmr_wallet_name' => env('ADMIN_XMR_WALLET_NAME', 'admin_xmr'),

    /*
    |--------------------------------------------------------------------------
    | Featured Listing Fee
    |--------------------------------------------------------------------------
    |
    | The amount in USD required to feature a listing for 30 days.
    | This will be converted to BTC or XMR based on the currency selected.
    |
    */
    'featured_listing_usd' => env('FEATURED_LISTING_FEE_USD', 10),

    /*
    |--------------------------------------------------------------------------
    | Early Finalization Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the early finalization feature.
    |
    */
    'early_finalization' => [
        'min_vendor_level' => env('EARLY_FINALIZATION_MIN_VENDOR_LEVEL', 8),
        'max_dispute_rate_percentage' => env('EARLY_FINALIZATION_MAX_DISPUTE_RATE', 20.0),
        'require_pgp_key' => env('EARLY_FINALIZATION_REQUIRE_PGP', true),
    ],
];
