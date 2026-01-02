# Escrow Implementation Testing Guide

## Overview
This document provides step-by-step instructions for testing the new escrow system that has been implemented for the Teardrop marketplace.

## What Changed

### Core Changes
1. **Physical Wallet Isolation**: Each order now gets its own dedicated RPC wallet (Bitcoin Core or Monero wallet-rpc)
2. **Three-Stage Flow**: Funds move from Buyer → Escrow → Vendor (not directly buyer to vendor)
3. **Automatic Sync**: Background jobs monitor escrow wallets and auto-detect when funds arrive
4. **Admin Fees**: 3% automatically deducted during escrow release (configured in config/fees.php)

### New Components
- **EscrowWallet Model**: Database records for each escrow wallet
- **EscrowService**: Core business logic for create/fund/release/refund operations
- **Sync Jobs Updated**: Both SyncBitcoinWallets and SyncMoneroWallets now monitor escrow wallets

### Modified Files
- `app/Http/Controllers/OrderController.php` - Order creation now creates escrow wallet and funds it
- `app/Http/Controllers/Vendor/VendorController.php` - Order completion releases escrow
- `app/Models/Order.php` - Added escrow relationships
- `app/Jobs/SyncBitcoinWallets.php` - Syncs escrow wallets
- `app/Jobs/SyncMoneroWallets.php` - Syncs escrow wallets
- Database: New `escrow_wallets` table, `orders` table has `escrow_wallet_id` and `escrow_funded_at`

## Prerequisites

### 1. Bitcoin Core Setup
```bash
# Ensure Bitcoin Core is running with RPC enabled
bitcoin-cli getblockchaininfo

# Verify wallet creation works
bitcoin-cli createwallet "test_escrow_wallet"
bitcoin-cli unloadwallet "test_escrow_wallet"
```

### 2. Monero Setup
```bash
# Ensure monero-wallet-rpc is running
curl -X POST http://127.0.0.1:18082/json_rpc \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":"0","method":"get_version"}'

# Check wallet directory is writable
ls -la /path/to/monero/wallets/
```

### 3. Laravel Queue Worker
The sync jobs MUST be running for escrow detection:
```bash
# In a separate terminal, start the queue worker
php artisan queue:listen --tries=1

# OR use the dev script
composer run dev
```

### 4. Database Migration
Already completed (2026_01_02_193844_create_escrow_wallets_table)

## Testing Workflow

### Test 1: Bitcoin Order with Escrow

#### Step 1: Create Order
1. Login as a buyer user
2. Navigate to a listing with `payment_method = 'escrow'`
3. Click "Purchase" and fill order form
4. Submit order

**Expected Database Changes:**
```sql
-- Check order was created
SELECT id, status, total_price, escrow_wallet_id, escrow_funded_at 
FROM orders 
ORDER BY id DESC LIMIT 1;

-- Check escrow wallet was created
SELECT id, order_id, currency, wallet_name, address, balance, status 
FROM escrow_wallets 
WHERE order_id = <order_id>;

-- Check BTC wallet was created
SELECT id, name, address 
FROM btc_wallets 
WHERE name LIKE 'escrow_order_%';
```

**Expected Results:**
- Order status: `pending`
- escrow_wallet_id: Not null
- escrow_funded_at: NULL (not yet funded)
- escrow_wallets.status: `active`
- escrow_wallets.balance: 0 (initially)
- btc_wallets record exists with name `escrow_order_{id}_btc`

#### Step 2: Check Transaction Sent
```sql
-- Check transaction was created
SELECT id, user_id, btc_wallet_id, type, amount, txid, status 
FROM btc_transactions 
WHERE type = 'escrow_fund' 
ORDER BY id DESC LIMIT 1;
```

**Expected:**
- txid: Not null (blockchain transaction hash)
- status: `pending` or `completed`
- amount: Matches order total_price

#### Step 3: Wait for Sync Job
The `SyncBitcoinWallets` job runs every minute (configured in app/Console/Kernel.php):
```bash
# Watch the logs
tail -f storage/logs/laravel.log | grep -i escrow

# OR manually trigger sync
php artisan schedule:run
```

**Expected Log Output:**
```
[timestamp] local.DEBUG: Found X active Bitcoin escrow wallets to sync
[timestamp] local.DEBUG: Syncing escrow wallet: escrow_order_123_btc
[timestamp] local.INFO: Escrow wallet funded for order #123
```

**Database After Sync:**
```sql
SELECT escrow_funded_at, status 
FROM orders 
WHERE id = <order_id>;
```

**Expected:**
- escrow_funded_at: NOW() (timestamp set by sync job)
- escrow_wallets.balance: > 0 (updated from blockchain)

#### Step 4: Complete Order
1. Login as vendor OR buyer (both can complete)
2. Navigate to order details page
3. Click "Complete Order"

**Expected Database Changes:**
```sql
-- Check order completed
SELECT status FROM orders WHERE id = <order_id>;
-- Result: 'completed'

-- Check escrow released
SELECT status, released_at FROM escrow_wallets WHERE order_id = <order_id>;
-- Result: status='released', released_at=NOW()

-- Check vendor received funds (97%)
SELECT balance FROM btc_wallets WHERE user_id = <vendor_id>;
-- Result: balance increased by (order_total * 0.97)

-- Check admin received fee (3%)
SELECT balance FROM btc_wallets WHERE name = 'admin_btc_wallet';
-- Result: balance increased by (order_total * 0.03)
```

### Test 2: Monero Order with Escrow

Follow same steps as Test 1, but:
- Currency: Monero (XMR)
- Wallet name: `escrow_order_{id}_xmr.wallet`
- Check `xmr_wallets` and `xmr_transactions` tables instead

**Monero-Specific Checks:**
```sql
-- Check XMR wallet created with encrypted seed
SELECT id, name, seed_encrypted, user_id 
FROM xmr_wallets 
WHERE name LIKE 'escrow_order_%';

-- Verify user_id is NULL (not owned by any user)
-- Verify seed_encrypted is not empty (for wallet recovery)
```

### Test 3: Order Cancellation (Future Implementation)
Not yet implemented. Will require:
1. New route: `POST /orders/{order}/cancel`
2. Controller method calls `EscrowService->refundEscrow()`
3. Funds return to buyer's wallet

## Troubleshooting

### Issue: Escrow wallet not created
**Check:**
```bash
# Bitcoin
bitcoin-cli listwallets | grep escrow

# Monero
curl -X POST http://127.0.0.1:18082/json_rpc \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":"0","method":"get_wallet_directory"}'
```

**Solution:**
- Check Laravel logs: `storage/logs/laravel.log`
- Verify RPC connection in config files (config/bitcoinrpc.php, config/monero.php)
- Check RPC daemon is running and accessible

### Issue: Escrow never funded (escrow_funded_at stays NULL)
**Check:**
1. Queue worker is running: `ps aux | grep queue`
2. Sync job is scheduled: `php artisan schedule:list`
3. Transaction has confirmations on blockchain

**Solution:**
```bash
# Manually trigger sync
php artisan queue:work --once

# Check specific wallet balance
bitcoin-cli -rpcwallet=escrow_order_123_btc getbalance

# Monero: open wallet and check balance
curl -X POST http://127.0.0.1:18082/json_rpc \
  -d '{"jsonrpc":"2.0","id":"0","method":"open_wallet","params":{"filename":"escrow_order_123_xmr.wallet","password":"..."}}'
```

### Issue: Escrow release fails
**Check:**
```sql
-- Verify escrow has balance
SELECT balance FROM escrow_wallets WHERE order_id = <order_id>;

-- Verify escrow status is 'active'
SELECT status FROM escrow_wallets WHERE order_id = <order_id>;
```

**Solution:**
- Check vendor wallet exists: `SELECT * FROM btc_wallets WHERE user_id = <vendor_id>`
- Check admin wallet configured: See config/fees.php `admin_btc_wallet_name`
- Review Laravel logs for RPC errors

### Issue: Admin fee not received
**Check:**
```sql
-- Check admin wallet configuration
SELECT * FROM btc_wallets WHERE name = 'admin_btc_wallet';
```

**Solution:**
- Verify config/fees.php has correct admin wallet name
- Check `EscrowService->releaseEscrow()` logs
- Admin fee is 3% by default (config/fees.php `order_completion_fee_percentage`)

## Verification Checklist

After testing, verify:

- [ ] Escrow wallet created on order placement
- [ ] Funds transferred from buyer to escrow address
- [ ] Sync job detects incoming funds and sets escrow_funded_at
- [ ] Order completion releases funds to vendor (97%)
- [ ] Admin wallet receives 3% fee
- [ ] Escrow wallet marked as 'released'
- [ ] Order status changed to 'completed'
- [ ] All database foreign keys working
- [ ] No orphaned wallets (every escrow_wallet has an order)
- [ ] Wallet balances match blockchain reality

## Security Validation

### Critical Checks:
1. **Wallet Isolation**: 
   ```sql
   SELECT user_id FROM btc_wallets WHERE name LIKE 'escrow_order_%';
   -- ALL should be NULL (not owned by any user)
   ```

2. **Password Security**:
   ```sql
   SELECT wallet_password_hash FROM escrow_wallets LIMIT 5;
   -- ALL should be 64-character hashes (sha256)
   ```

3. **Balance Integrity**:
   ```bash
   # For each escrow wallet, verify blockchain balance matches DB
   bitcoin-cli -rpcwallet=escrow_order_123_btc getbalance
   # Compare with: SELECT balance FROM escrow_wallets WHERE order_id = 123
   ```

4. **Admin Fee Calculation**:
   ```sql
   -- Check total released equals order total
   SELECT 
       o.total_price,
       ew.balance as escrow_balance,
       (o.total_price * 0.97) as expected_vendor_amount,
       (o.total_price * 0.03) as expected_admin_fee
   FROM orders o
   JOIN escrow_wallets ew ON o.escrow_wallet_id = ew.id
   WHERE o.status = 'completed'
   LIMIT 5;
   ```

## Performance Testing

### Load Test: Multiple Concurrent Orders
```bash
# Create 10 orders simultaneously
for i in {1..10}; do
    curl -X POST http://localhost:8000/orders \
      -H "Authorization: Bearer $USER_TOKEN" \
      -d "listing_id=1&quantity=1" &
done
wait

# Check all escrow wallets created
SELECT COUNT(*) FROM escrow_wallets WHERE created_at > NOW() - INTERVAL 1 MINUTE;
-- Expected: 10
```

### Sync Job Performance
```sql
-- Check sync job execution time
SELECT 
    COUNT(*) as escrow_count,
    AVG(TIMESTAMPDIFF(SECOND, created_at, escrow_funded_at)) as avg_detection_seconds
FROM orders
WHERE escrow_funded_at IS NOT NULL;
```

**Expected:**
- Detection time: < 60 seconds (depends on sync job frequency)
- No duplicate wallet creations
- No race conditions in balance updates

## Rollback Plan

If issues arise, you can rollback:

```bash
# Rollback migration
php artisan migrate:rollback

# Or rollback specific migration
php artisan migrate:rollback --step=1

# Remove test escrow wallets from RPC
bitcoin-cli listwallets | grep escrow | xargs -I {} bitcoin-cli unloadwallet {}

# Monero: manually delete .wallet files
rm /path/to/monero/wallets/escrow_order_*.wallet*
```

## Next Steps

After successful testing:

1. **Implement Order Cancellation**:
   - Add `OrderController@cancel()` method
   - Call `EscrowService->refundEscrow()`
   - Add route and UI button

2. **Add Admin Dashboard**:
   - View all active escrow wallets
   - Monitor total escrow balance
   - Manual intervention for stuck escrows

3. **Enhance Monitoring**:
   - Add Slack/email alerts for large escrow amounts
   - Dashboard widget showing escrow health
   - Log all escrow operations to audit_logs table

4. **Production Deployment**:
   - Backup database before migration
   - Test on staging environment first
   - Monitor logs for 24-48 hours after deployment
   - Have rollback plan ready

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Review [ESCROW_IMPLEMENTATION_PLAN.md](ESCROW_IMPLEMENTATION_PLAN.md)
- Review security audit: [MONERO_AUDIT_REPORT.md](MONERO_AUDIT_REPORT.md)
- Bitcoin RPC docs: https://developer.bitcoin.org/reference/rpc/
- Monero RPC docs: https://www.getmonero.org/resources/developer-guides/wallet-rpc.html
