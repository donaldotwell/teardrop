<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monero Wallet RPC Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to monero-wallet-rpc.
    | The RPC must be started with --wallet-dir so that any wallet file
    | in the directory can be opened/closed on demand.
    |
    | Example daemon command:
    |   monero-wallet-rpc --testnet --wallet-dir ~/.monero/testnet/wallets \
    |     --rpc-bind-port 28088 --rpc-bind-ip 127.0.0.1 \
    |     --daemon-address 127.0.0.1:28081 --trusted-daemon \
    |     --disable-rpc-login --detach
    |
    */

    'scheme' => env('MONERO_RPC_SCHEME', 'http'),
    'host' => env('MONERO_RPC_HOST', '127.0.0.1'),
    'port' => env('MONERO_RPC_PORT', 28088),
    'user' => env('MONERO_RPC_USER', ''),
    'password' => env('MONERO_RPC_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Wallet Directory
    |--------------------------------------------------------------------------
    |
    | Directory where Monero wallet files are stored on disk.
    | This must match the --wallet-dir path passed to monero-wallet-rpc.
    |
    */

    'wallet_dir' => env('MONERO_WALLET_DIR', storage_path('monero/wallets')),

    /*
    |--------------------------------------------------------------------------
    | Wallet Password Salt
    |--------------------------------------------------------------------------
    |
    | Extra entropy mixed into per-wallet passwords. Each wallet's password
    | is derived as: hash('sha256', user_id . salt . APP_KEY)
    | then encrypted with Crypt::encryptString() for storage.
    |
    */

    'wallet_password_salt' => env('MONERO_WALLET_PASSWORD_SALT', 'teardrop-xmr-wallet'),

    /*
    |--------------------------------------------------------------------------
    | RPC Lock Settings
    |--------------------------------------------------------------------------
    |
    | Only one wallet can be open at a time on monero-wallet-rpc.
    | All operations acquire a global lock before opening a wallet.
    |
    */

    'rpc_lock_timeout' => env('MONERO_RPC_LOCK_TIMEOUT', 60),       // Max seconds to hold the lock
    'rpc_lock_wait_timeout' => env('MONERO_RPC_LOCK_WAIT', 30),     // Max seconds to wait for lock acquisition

    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    |
    */

    'sync_enabled' => env('MONERO_SYNC_ENABLED', true),
    'sync_interval' => env('MONERO_SYNC_INTERVAL', 120), // seconds between sync runs
    'sync_idle_skip_days' => env('MONERO_SYNC_IDLE_SKIP_DAYS', 30), // Skip wallets idle longer than this

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

    'network' => env('MONERO_NETWORK', 'testnet'),
];
