# Monero Architecture Overview

## Current System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     MONERO-WALLET-RPC                          │
│                  (Master Wallet: teardrop_master)               │
│                                                                 │
│  Account 0 (ONLY ACCOUNT)                                      │
│  ├── Subaddress 0  (admin)                                     │
│  ├── Subaddress 1  (user1)                                     │
│  ├── Subaddress 2  (user2)                                     │
│  ├── Subaddress 3  (user3 - rotated)                           │
│  ├── Subaddress 4  (user3 - rotated)                           │
│  ├── Subaddress 5  (escrow_order_123)                          │
│  └── ...                                                         │
└─────────────────────────────────────────────────────────────────┘
                              ▲
                              │ RPC Calls
                              │ (HTTP Basic Auth)
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    LARAVEL APPLICATION                          │
│                                                                 │
│  MoneroRepository                                               │
│  ├── rpcCall()                                                  │
│  ├── createSubaddress()                                         │
│  ├── getBalance()                                               │
│  ├── transfer()                                                 │
│  └── sweep() (TO IMPLEMENT)                                     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ Eloquent ORM
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        DATABASE                                 │
│                                                                 │
│  users                     xmr_wallets                          │
│  ├── id                    ├── id                               │
│  ├── username_pub          ├── user_id (FK)                     │
│  └── ...                   ├── name = 'teardrop_master'         │
│                            ├── primary_address                  │
│                            ├── balance (DEPRECATED)             │
│                            └── ...                              │
│                                     │                           │
│                                     │ 1:N                       │
│                                     ▼                           │
│                            xmr_addresses                        │
│                            ├── id                               │
│                            ├── xmr_wallet_id (FK)               │
│                            ├── address                          │
│                            ├── account_index = 0                │
│                            ├── address_index (unique)           │
│                            ├── label                            │
│                            ├── balance                          │
│                            ├── is_used                          │
│                            └── ...                              │
│                                     │                           │
│                                     │ 1:N                       │
│                                     ▼                           │
│                            xmr_transactions                     │
│                            ├── id                               │
│                            ├── xmr_wallet_id (FK)               │
│                            ├── xmr_address_id (FK)              │
│                            ├── txid                             │
│                            ├── type (deposit/withdrawal)        │
│                            ├── amount                           │
│                            ├── status (pending/confirmed/unlocked)│
│                            └── ...                              │
└─────────────────────────────────────────────────────────────────┘
```

## Balance Calculation Flow

### ❌ CURRENT (BROKEN)
```
User requests balance
    │
    └──> User::getBalance()
            │
            └──> xmrWallet->balance
                     │
                     └──> Value from RPC sync (SLOW, UNRELIABLE)
```

### ✅ CORRECT (TO IMPLEMENT)
```
User requests balance
    │
    └──> User::getBalance()
            │
            └──> SUM(xmr_transactions)
                     │
                     └──> WHERE status = 'unlocked'
                             AND (type = 'deposit' OR type = 'withdrawal')
                             
Fast DB query (<10ms) vs. RPC call (500-2000ms)
```

## Order Payment Flow

### Current Implementation (Single Address)
```
1. User clicks "Buy"
2. Check total balance ✓
3. Attempt payment from single address ✗ FAILS if funds split
```

### Required Implementation (Multi-Address)
```
1. User clicks "Buy"
2. Check total balance ✓
3. Find addresses with funds:
   ├── Address 1: 0.5 XMR
   ├── Address 2: 0.3 XMR
   └── Address 3: 0.8 XMR
4. Select addresses until amount covered:
   ├── Need: 1.2 XMR
   ├── Use Address 1 (0.5) + Address 2 (0.3) + Address 3 (0.4) = 1.2 XMR
   └── Call MoneroRepository::sweepAddresses([1,2,3], destination, 1.2)
5. Create XmrTransaction records:
   ├── Address 1: -0.5 XMR (withdrawal)
   ├── Address 2: -0.3 XMR (withdrawal)
   └── Address 3: -0.4 XMR (withdrawal)
6. Update order status
```

## Escrow Flow

### Order Creation (Direct Payment)
```
Buyer                    Vendor
  │                        │
  │  1. Place Order        │
  │  (Amount: 1.5 XMR)     │
  │                        │
  │  2. Sweep from         │
  │     buyer addresses    │
  │     → vendor address   │
  │     (97% = 1.455 XMR)  │
  │                        │
  │  3. Send admin fee     │
  │     → admin address    │
  │     (3% = 0.045 XMR)   │
  │                        │
  │                        │  4. Order marked
  │                        │     "completed"
  │                        ▼
  │                      Vendor receives
  │                      funds instantly
```

### Order Creation (Escrow)
```
Buyer                 Escrow                Vendor
  │                     │                     │
  │  1. Place Order     │                     │
  │  (Amount: 1.5 XMR)  │                     │
  │                     │                     │
  │  2. Send to         │                     │
  │     escrow address  │  3. Funds held      │
  │  ──────────────────>│     in escrow       │
  │                     │                     │
  │  4. Mark shipped    │                     │
  │                     │<────────────────────│
  │                     │                     │
  │  5. Confirm receipt │                     │
  │  (Complete order)   │                     │
  │  ──────────────────>│                     │
  │                     │                     │
  │                     │  6. Release:        │
  │                     │     97% → vendor    │
  │                     │     3%  → admin     │
  │                     │  ──────────────────>│
  │                     │                     ▼
  │                     │                   Vendor
  │                     │                   receives
  │                     │                   1.455 XMR
```

## Feature Listing Payment Flow

### Bitcoin (Implemented)
```
Vendor                 Admin
  │                      │
  │  1. Request feature  │
  │     (Fee: $10)       │
  │                      │
  │  2. Send BTC         │
  │  ──────────────────> │
  │                      │
  │  3. Listing marked   │
  │     as featured      │
  └──────────────────────┘
```

### Monero (TO IMPLEMENT)
```
Vendor                 Admin
  │                      │
  │  1. Request feature  │
  │     (Fee: $10)       │
  │                      │
  │  2. Calculate XMR    │
  │     equivalent       │
  │     ($10 / rate)     │
  │                      │
  │  3. Sweep from       │
  │     vendor addresses │
  │  ──────────────────> │
  │                      │
  │  4. Listing marked   │
  │     as featured      │
  └──────────────────────┘
```

## Transaction Status Lifecycle

```
┌─────────────┐
│  Pending    │ ← Transaction detected on blockchain
│  (0 conf)   │   (NOT counted in balance)
└──────┬──────┘
       │
       │ After 1+ confirmations
       ▼
┌─────────────┐
│ Confirmed   │ ← Transaction has confirmations
│ (1-9 conf)  │   (NOT counted in balance)
└──────┬──────┘
       │
       │ After 10+ confirmations
       ▼
┌─────────────┐
│  Unlocked   │ ← Funds are spendable
│ (10+ conf)  │   (✓ COUNTED in balance)
└─────────────┘
```

## Cleanup Command Flow

```
┌─────────────────────────────────────────────────────────────┐
│                php artisan monero:cleanup                   │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  Step 1: Ensure All Users Have XmrWallet                   │
│  ├── Query users without xmrWallet                          │
│  ├── Create XmrWallet records                               │
│  └── Link to master wallet                                  │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  Step 2: Ensure All Wallets Have Addresses                 │
│  ├── Query wallets without addresses                        │
│  ├── Call MoneroRepository::createSubaddress()              │
│  └── Create XmrAddress records                              │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  Step 3: Sync Address Balances (--sync-addresses)          │
│  ├── For each address:                                      │
│  │   ├── Query RPC get_balance                             │
│  │   ├── Update XmrAddress.balance                          │
│  │   └── Mark is_used if balance > 0                        │
│  └── Report synced addresses                                │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  Step 4: Rebuild Transactions (--rebuild-transactions)     │
│  ├── For each address:                                      │
│  │   ├── Query RPC incoming_transfers                      │
│  │   ├── Check if transaction exists in DB                 │
│  │   └── Create XmrTransaction if missing                   │
│  └── Report created transactions                            │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  Step 5: Verify Balances (--verify)                        │
│  ├── Calculate DB balance (SUM transactions)                │
│  ├── Calculate RPC balance (query addresses)                │
│  ├── Compare and report discrepancies                       │
│  └── Display table of results                               │
└─────────────────────────────────────────────────────────────┘
```

## Address Rotation Strategy

```
User Wallet Timeline:

Day 1:  Address 1 (unused)
        └── Receives deposit: 1.0 XMR
        
Day 2:  Address 1 (used, balance: 1.0)
        Address 2 (unused) ← New address generated
        
Day 3:  Address 1 (used, balance: 0.3) ← Spent 0.7
        Address 2 (used, balance: 0.5) ← Received deposit
        Address 3 (unused) ← New address generated
        
Strategy:
- Always have at least 1 unused address
- Generate new address when current marked as used
- Label each address with user info for tracking
```

## Multi-Wallet vs Single-Wallet Architecture

### ❌ Old Approach (Bitcoin-style)
```
Each user has separate wallet file:
├── user1_wallet.dat
├── user2_wallet.dat
└── user3_wallet.dat

Problems:
- Need to open/close wallets
- Sync takes forever
- High disk usage
- Complex management
```

### ✅ Current Approach (Monero-optimized)
```
Single master wallet with subaddresses:
teardrop_master
└── Account 0
    ├── Subaddress 0 (admin)
    ├── Subaddress 1 (user1)
    ├── Subaddress 2 (user2)
    └── Subaddress 3 (user3)

Benefits:
- One wallet to sync
- Fast address generation
- Efficient RPC usage
- Easy backup (one seed)
```

## Key Differences: Bitcoin vs Monero

| Feature | Bitcoin | Monero |
|---------|---------|--------|
| Wallet Architecture | One wallet per user | Shared master wallet |
| Address Type | HD wallet addresses | Subaddresses |
| Balance Tracking | `btc_wallets.balance` | `SUM(xmr_transactions)` |
| Privacy | Public ledger | Private by default |
| Atomic Unit | 1 BTC = 100,000,000 sats | 1 XMR = 1,000,000,000,000 piconero |
| Confirmations | 1-6 recommended | 10 required for unlock |
| Multi-input TX | Common | Use sweep_all or transfer |

## Security Considerations

### Master Wallet Password
```
Location: config/monero.php
Value: hash('sha256', config('app.key'))

⚠️ CRITICAL: If app.key changes, wallet cannot be opened!
           Backup wallet seed encrypted in xmr_wallets.seed_encrypted
```

### Transaction Verification
```
1. RPC returns transaction → Create XmrTransaction (status: pending)
2. After 1+ confirmations → Update status: confirmed
3. After 10+ confirmations → Update status: unlocked (ADD to balance)
4. User balance = SUM(unlocked transactions)
```

### Race Condition Prevention
```php
// Lock wallet before spending
$wallet = XmrWallet::where('id', $walletId)
    ->lockForUpdate()
    ->first();

// Calculate available balance
$balance = XmrTransaction::where('xmr_wallet_id', $wallet->id)
    ->where('status', 'unlocked')
    ->sum(/* ... */);

// Verify sufficient funds
if ($balance < $amount) {
    DB::rollBack();
    throw new InsufficientFundsException();
}

// Proceed with transaction...
```

## Monitoring & Maintenance

### Health Checks
```
Daily:
- Verify RPC is running
- Check for balance discrepancies
- Monitor failed transactions

Weekly:
- Run monero:cleanup --verify
- Sync address balances
- Review transaction logs

Monthly:
- Full audit with --rebuild-transactions
- Verify all users can transact
- Check disk usage
```

### Alert Triggers
```
⚠️ RPC Down → Page cannot load, immediate fix needed
⚠️ Balance Mismatch → Run cleanup --verify
⚠️ Transaction Stuck → Check confirmations, may need manual fix
⚠️ Address Generation Failed → Verify wallet RPC access
```

## Performance Optimization

### Database Indexes
```sql
-- Critical for fast balance queries
CREATE INDEX idx_xmr_tx_balance 
ON xmr_transactions(xmr_wallet_id, status, type);

-- Critical for address lookups
CREATE INDEX idx_xmr_addr_wallet 
ON xmr_addresses(xmr_wallet_id, is_used, address_index);
```

### Caching Strategy
```php
// Cache user balance for 30 seconds
Cache::remember("xmr_balance_{$userId}", 30, function() use ($userId) {
    return User::find($userId)->getBalance();
});

// Invalidate cache on transaction
XmrTransaction::created(function($transaction) {
    Cache::forget("xmr_balance_{$transaction->wallet->user_id}");
});
```

---

This architecture document provides a complete visual overview of the Monero system structure, flows, and best practices.
