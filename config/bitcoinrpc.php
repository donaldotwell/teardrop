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
    | Transaction Fee Configuration
    |--------------------------------------------------------------------------
    |
    | Fee tiers determine the transaction fee rate (sat/vB) based on the
    | transaction amount in BTC. Higher amounts get higher priority fees
    | for faster confirmation times.
    |
    */

    'fee_tiers' => [
        ['min' => 0.0001, 'max' => 0.001, 'rate' => 5],   // ~20-40 min (3-4 blocks)
        ['min' => 0.001, 'max' => 0.01, 'rate' => 10],    // ~10-20 min (1-2 blocks)
        ['min' => 0.01, 'max' => 0.1, 'rate' => 20],      // ~10-15 min (1 block)
        ['min' => 0.1, 'max' => 1.0, 'rate' => 30],       // ~10 min (next block)
        ['min' => 1.0, 'max' => null, 'rate' => 50],      // ~10 min (priority next block)
    ],

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
