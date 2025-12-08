<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bitcoin RPC Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Bitcoin RPC connection using denpa/laravel-bitcoinrpc
    |
    */

    'scheme' => env('BITCOIN_RPC_SCHEME', 'http'),
    'host' => env('BITCOIN_RPC_HOST', 'localhost'),
    'port' => env('BITCOIN_RPC_PORT', 8332),
    'user' => env('BITCOIN_RPC_USER'),
    'password' => env('BITCOIN_RPC_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Bitcoin Network Configuration
    |--------------------------------------------------------------------------
    */

    'network' => env('BITCOIN_NETWORK', 'mainnet'), // mainnet, testnet, regtest
    'confirmations_required' => env('BITCOIN_CONFIRMATIONS_REQUIRED', 6),
    'min_deposit_amount' => env('BITCOIN_MIN_DEPOSIT', 0.0001), // Minimum deposit in BTC

    /*
    |--------------------------------------------------------------------------
    | Wallet Configuration
    |--------------------------------------------------------------------------
    */

    'wallet_prefix' => env('BITCOIN_WALLET_PREFIX', 'user_'),
    'auto_generate_addresses' => env('BITCOIN_AUTO_GENERATE_ADDRESSES', true),
    'address_gap_limit' => env('BITCOIN_ADDRESS_GAP_LIMIT', 20),

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    */

    'sync_interval' => env('BITCOIN_SYNC_INTERVAL', 60), // seconds
    'sync_enabled' => env('BITCOIN_SYNC_ENABLED', true),
    'sync_batch_size' => env('BITCOIN_SYNC_BATCH_SIZE', 100),
];
