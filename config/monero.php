<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monero Wallet RPC Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to monero-wallet-rpc
    |
    */

    'scheme' => env('MONERO_RPC_SCHEME', 'http'),
    'host' => env('MONERO_RPC_HOST', 'localhost'),
    'port' => env('MONERO_RPC_PORT', 18082),
    'user' => env('MONERO_RPC_USER', 'monero'),
    'password' => env('MONERO_RPC_PASSWORD', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Wallet Directory
    |--------------------------------------------------------------------------
    |
    | Directory where Monero wallet files are stored
    |
    */

    'wallet_dir' => env('MONERO_WALLET_DIR', storage_path('monero/wallets')),
    
    /*
    |--------------------------------------------------------------------------
    | Default Wallet Password
    |--------------------------------------------------------------------------
    |
    | Default password for creating new wallets (should be strong and unique)
    |
    */

    'default_wallet_password' => env('MONERO_WALLET_PASSWORD', 'changeme'),
    
    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    |
    */

    'sync_enabled' => env('MONERO_SYNC_ENABLED', true),
    'sync_interval' => env('MONERO_SYNC_INTERVAL', 60), // seconds
    
    /*
    |--------------------------------------------------------------------------
    | Master Wallet Configuration
    |--------------------------------------------------------------------------
    |
    | Single wallet loaded in wallet-rpc with subaddresses for all users
    |
    */

    'master_wallet_name' => env('MONERO_MASTER_WALLET_NAME', 'teardrop_master'),
    'master_wallet_password' => env('MONERO_MASTER_WALLET_PASSWORD', null),
    
    /*
    |--------------------------------------------------------------------------
    | Confirmation Settings
    |--------------------------------------------------------------------------
    |
    */

    'min_confirmations' => env('MONERO_MIN_CONFIRMATIONS', 10),
    
    /*
    |--------------------------------------------------------------------------
    | Testing/Development Settings
    |--------------------------------------------------------------------------
    |
    | Force transactions to confirmed status without waiting for blockchain
    |
    */

    'force_confirmations' => env('MONERO_FORCE_CONFIRMATIONS', false),
    
    /*
    |--------------------------------------------------------------------------
    | Network
    |--------------------------------------------------------------------------
    |
    | Network type: mainnet, testnet, stagenet
    |
    */

    'network' => env('MONERO_NETWORK', 'mainnet'),
];
