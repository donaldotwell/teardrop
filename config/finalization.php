<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Early Finalization Enabled
    |--------------------------------------------------------------------------
    |
    | Global toggle for the early finalization feature. Set to false to
    | disable early finalization system-wide.
    |
    */
    'enabled' => env('EARLY_FINALIZATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Minimum Vendor Level
    |--------------------------------------------------------------------------
    |
    | The default minimum vendor level required for early finalization.
    | Can be overridden per product category.
    |
    */
    'default_min_vendor_level' => env('EARLY_FINALIZATION_DEFAULT_MIN_VENDOR_LEVEL', 8),

    /*
    |--------------------------------------------------------------------------
    | Default Dispute Window Duration
    |--------------------------------------------------------------------------
    |
    | Default dispute window duration in minutes (7 days = 10080 minutes).
    |
    */
    'default_dispute_window_minutes' => env('EARLY_FINALIZATION_DEFAULT_WINDOW', 10080),

    /*
    |--------------------------------------------------------------------------
    | Warning Threshold
    |--------------------------------------------------------------------------
    |
    | Show warning to users when dispute window expires within this many minutes.
    |
    */
    'warning_threshold_minutes' => env('EARLY_FINALIZATION_WARNING_THRESHOLD', 60),

    /*
    |--------------------------------------------------------------------------
    | Auto-Disable on High Dispute Rate
    |--------------------------------------------------------------------------
    |
    | Automatically disable early finalization for vendors with high dispute rates.
    |
    */
    'auto_disable_on_high_dispute_rate' => env('EARLY_FINALIZATION_AUTO_DISABLE', false),

    /*
    |--------------------------------------------------------------------------
    | Maximum Dispute Rate Percentage
    |--------------------------------------------------------------------------
    |
    | Maximum allowed dispute rate percentage for early finalized orders.
    | If auto-disable is enabled, vendors exceeding this will be disabled.
    |
    */
    'max_dispute_rate_percentage' => env('EARLY_FINALIZATION_MAX_DISPUTE_RATE', 20.0),
];
