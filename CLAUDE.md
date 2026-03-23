# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Local Development
```bash
composer dev          # Start all dev services concurrently (server, queue, logs, vite)
php artisan serve     # Web server only
npm run dev           # Vite frontend assets only
php artisan queue:listen --tries=1  # Queue worker
```

### Testing
```bash
php artisan test                          # Run all tests
php artisan test tests/Feature/ForumTest.php  # Run a single test file
php artisan test --filter=MethodName      # Run a single test by name
vendor/bin/phpunit                        # Run via PHPUnit directly
```

Note: Tests use a real MySQL database (SQLite is commented out in `phpunit.xml`). Ensure the DB is running before testing.

### Database
```bash
php artisan migrate
php artisan db:seed
php artisan db:seed --class=FundUserWallets
php artisan migrate:fresh --seed
```

### Docker (alternative to local)
```bash
make install     # Full initial setup
make up / make down
make test        # Run tests inside container
make fresh       # migrate:fresh --seed inside container
make shell       # Enter app container
```

### Code Quality
```bash
vendor/bin/pint   # Laravel Pint (code style fixer)
```

## Architecture

### Overview
Laravel 11 escrow marketplace with multi-cryptocurrency support (Bitcoin and Monero). Users can be buyers, vendors, staff, or admins with distinct role-based interfaces.

### Route Structure
Routes are split across four files, each with their own middleware groups:
- `routes/web.php` — public and buyer routes
- `routes/vendor.php` — vendor-only routes (listing/product management)
- `routes/admin.php` — admin panel routes
- `routes/moderator.php` — moderator and staff routes

**Laravel 11 critical note**: Constructor-based middleware (`$this->middleware()`) is removed. All middleware must be registered at the route level.

### User Roles & Permissions
- `is_admin` / `is_vendor` / `is_banned` flags on the `User` model
- Role-based access via `App\Repositories\RolesRepository` and `PermissionsRepository`
- Staff and moderators have separate controller namespaces: `App\Http\Controllers\Staff\` (handles support tickets) and `App\Http\Controllers\Staff\` moderator controllers

### Payment & Wallet Architecture
Two separate cryptocurrency systems:

**Bitcoin** (`BtcWallet`, `BtcAddress`, `BtcTransaction`):
- Uses `denpa/laravel-bitcoinrpc` to communicate with a Bitcoin Core node via RPC
- Per-user wallets with auto-generated addresses
- `App\Repositories\BitcoinRepository` handles all RPC calls

**Monero** (`XmrWallet`, `XmrAddress`, `XmrTransaction`):
- Uses monero-wallet-rpc running with `--wallet-dir` (per-user wallet directory architecture)
- Per-user subaddresses within a shared wallet RPC
- `App\Repositories\MoneroRepository` handles all RPC calls
- Configured via `MONERO_RPC_*` env vars

**Escrow** (`EscrowWallet`):
- Each escrow order gets an `EscrowWallet` record linked to the `Order`
- `App\Services\EscrowService` manages fund locking, release, and refunds
- Orders have two payment methods: `direct` (immediate) or `escrow` (held until completion)

### Order Lifecycle
`Order` statuses: `pending` → `completed` | `cancelled` | `disputed`

Key order concepts:
- **Early finalization**: Vendors in eligible categories can request early fund release; creates a dispute window (`dispute_window_expires_at`) during which buyers can still dispute
- **Escrow hold**: Active disputes on escrow orders prevent fund release (`shouldHoldEscrow()`)
- **Finalization windows**: Configurable per-category via `FinalizationWindow` model

### Dispute System
`Dispute` statuses: `open` → `under_review` → `waiting_buyer` | `waiting_vendor` → `escalated` → `resolved` | `closed`

Disputes belong to an `Order` and involve two parties (buyer/vendor). Admins/moderators can be assigned. Evidence files are stored via Laravel Storage.

### Services Layer
- `App\Services\EscrowService` — escrow fund management
- `App\Services\FinalizationService` — early finalization logic
- `App\Services\ForumModerationService` — forum content moderation

### Frontend
Pure Blade templates with Tailwind CSS utility classes. **No JavaScript, no SVGs, no icon libraries** — not even inline `onclick` handlers or `<script>` tags. All interactivity must be achieved through standard HTML forms and server-side logic. Color theme: amber/yellow for primary UI, admin, and moderator interfaces.

### Key Config
- `ORDER_COMPLETION_FEE_PERCENT` — platform fee on completed orders (default 3%)
- `VENDOR_CONVERSION_FEE_USD` — fee to become a vendor (default $1000)
- `BITCOIN_CONFIRMATIONS_REQUIRED` / `MONERO_MIN_CONFIRMATIONS` — on-chain confirmation thresholds
- `helpers/functions.php` — global helper functions (auto-loaded via composer)
