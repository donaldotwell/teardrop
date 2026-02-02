# Phase 3: Multi-Address Payment Logic - COMPLETE ✓

## Summary
Successfully implemented multi-address payment functionality for Monero transactions. The system can now handle scenarios where a user's balance is spread across multiple subaddresses.

## Changes Made

### 1. MoneroRepository.php - New Methods

#### `getAddressBalance($accountIndex, $addressIndex)`
- Queries RPC for a specific subaddress balance
- Returns balance, unlocked_balance, and number of unspent outputs
- Used for verifying which addresses have spendable funds

```php
$balance = MoneroRepository::getAddressBalance(0, 5);
// Returns: ['balance' => 1.5, 'unlocked_balance' => 1.2, 'num_unspent_outputs' => 3]
```

#### `findAddressesForPayment($wallet, $amount)`
- Intelligently selects addresses that can cover a payment amount
- Orders addresses by balance (highest first) for efficiency
- Verifies actual RPC balance before selecting (prevents stale DB data issues)
- Returns array of address details if sufficient funds found, empty array if not

```php
$addresses = MoneroRepository::findAddressesForPayment($wallet, 2.5);
// Returns: [
//   ['address_index' => 1, 'account_index' => 0, 'balance' => 1.8],
//   ['address_index' => 3, 'account_index' => 0, 'balance' => 0.9],
// ]
```

#### `sweepAddresses($sourceIndices, $accountIndex, $destination, $amount)`
- Consolidates funds from multiple subaddresses into a single payment
- Uses Monero's `transfer` RPC with multiple `subaddr_indices`
- Returns transaction details: tx_hash, fee, amount, tx_key
- Comprehensive logging for debugging and auditing

```php
$result = MoneroRepository::sweepAddresses([1, 3, 5], 0, $destinationAddress, 2.5);
// Returns: ['tx_hash' => '...', 'fee' => 0.0001, 'amount' => 2.5, 'tx_key' => '...']
```

### 2. EscrowService.php - Updated Payment Logic

#### `fundMoneroEscrow()` 
**Before**: Attempted payment from first address only, would fail if insufficient.

**After**: 
- Calls `findAddressesForPayment()` to identify which addresses have funds
- Single address path: Uses standard `transfer()` if one address has enough
- Multi-address path: Uses `sweepAddresses()` to consolidate funds
- Proper error messages distinguish between different failure scenarios

```php
// Old behavior:
$txid = MoneroRepository::transfer($wallet->name, $destination, $amount);
// Failed if address #0 didn't have enough, even if address #1 did

// New behavior:
$sourceAddresses = MoneroRepository::findAddressesForPayment($wallet, $amount);
if (count($sourceAddresses) === 1) {
    // Use efficient single-address transfer
} else {
    // Use multi-address sweep
}
```

#### `processDirectMoneroPayment()`
**Before**: Direct vendor payments failed with split balances.

**After**:
- Finds addresses with sufficient combined balance for total order amount
- Sends vendor payment (97%) using single or multi-address logic
- Sends admin fee (3%) separately
- Logs which addresses were used for accountability

## How It Works

### Single Address Scenario (No Change)
```
User has:
  Address 0: 5.0 XMR ✓
  Address 1: 0.0 XMR
  
Order needs: 2.5 XMR

Result: Standard transfer from Address 0
Transaction count: 1
```

### Multi-Address Scenario (NEW)
```
User has:
  Address 0: 1.2 XMR
  Address 1: 0.8 XMR
  Address 2: 1.5 XMR
  
Order needs: 2.5 XMR

Step 1: findAddressesForPayment selects addresses [0, 2] (total: 2.7 XMR)
Step 2: sweepAddresses consolidates funds in single transaction
Result: Payment sent from multiple addresses in ONE transaction
Transaction count: 1 (Monero handles multi-input internally)
```

## Testing Scenarios

### Test Case 1: Single Address Sufficient
```php
// Setup: Address 0 has 10 XMR
// Action: Place order for 2 XMR
// Expected: Standard transfer, no sweep needed
// Verify: Only address 0 balance decreases
```

### Test Case 2: Multi-Address Required
```php
// Setup: Address 0 has 1 XMR, Address 1 has 1.5 XMR
// Action: Place order for 2 XMR
// Expected: Sweep from both addresses
// Verify: Both address balances decrease, vendor receives 2 XMR (minus fees)
```

### Test Case 3: Insufficient Total Balance
```php
// Setup: Address 0 has 0.5 XMR, Address 1 has 0.3 XMR
// Action: Place order for 2 XMR
// Expected: Error "Insufficient Monero balance across all addresses"
// Verify: No transaction created, order not placed
```

### Test Case 4: Locked Balance
```php
// Setup: Address 0 has 2 XMR (5 confirmations, still locked)
// Action: Place order for 1 XMR
// Expected: Error "Insufficient Monero balance" (unlocked only counts)
// Verify: Only unlocked_balance is considered for spending
```

## Performance Considerations

### Database Queries
- Initial query fetches addresses with `balance > 0` (indexed)
- Ordered by balance DESC for efficiency (largest addresses first)
- Minimal RPC calls (only for selected addresses)

### RPC Efficiency
- Single `get_balance` call per address candidate
- Sweep uses ONE `transfer` RPC call regardless of address count
- Fee calculation handled by Monero daemon automatically

### Logging
All multi-address operations logged with:
- Order ID
- Number of addresses used
- Address indices
- Total amount
- Transaction hash

Example log:
```
[INFO] Using multi-address payment for escrow
  order_id: 123
  num_addresses: 3
  address_indices: [0, 2, 5]
  amount: 2.5 XMR
  
[INFO] Successfully swept addresses
  tx_hash: a1b2c3...
  fee: 0.0001 XMR
  amount_sent: 2.5 XMR
```

## Error Handling

### New Error Messages
1. **"Insufficient Monero balance across all addresses"**
   - User's total balance is less than required amount
   - Action: Top up wallet or reduce order quantity

2. **"Failed to sweep addresses for escrow payment"**
   - RPC call succeeded but returned unexpected data
   - Action: Check monero-wallet-rpc logs, may need to retry

3. **"No source addresses provided for sweep"**
   - Internal error: findAddressesForPayment returned empty unexpectedly
   - Action: Check wallet has addresses, verify RPC connectivity

## Integration Points

### Already Using Multi-Address Logic:
- ✅ Escrow order creation (`fundMoneroEscrow`)
- ✅ Direct vendor payments (`processDirectMoneroPayment`)

### Future Integration Points:
- 🔄 Feature listing payments (Phase 4)
- 🔄 Vendor conversion payments
- 🔄 Dispute refunds
- 🔄 Manual withdrawals

## Configuration

No new configuration required. Uses existing:
- `config/monero.php` - RPC connection settings
- `config/fees.php` - Admin fee percentage (3%)

## Database Impact

No schema changes required. Leverages existing:
- `xmr_addresses` - Address balance tracking
- `xmr_transactions` - Transaction history
- `xmr_wallets` - User wallet records

## Security Notes

### Race Condition Prevention
Current implementation uses database-level locking in controllers:
```php
$wallet = XmrWallet::where('id', $walletId)->lockForUpdate()->first();
```
This prevents double-spending when checking balances.

### Balance Verification
`findAddressesForPayment()` queries RPC for real-time balances, not relying on potentially stale database values. This ensures accurate spending calculations.

### Transaction Atomicity
Monero's `transfer` RPC with multiple `subaddr_indices` creates a SINGLE atomic transaction. Either all inputs are spent or none are - no partial failures.

## Monitoring & Debugging

### Check if Multi-Address Logic Was Used
```bash
# Search logs for multi-address payments
grep "Using multi-address payment" storage/logs/laravel.log

# Check specific order
grep "order_id: 123" storage/logs/laravel.log | grep sweep
```

### Verify Address Selection
```bash
# See which addresses were selected for payment
grep "findAddressesForPayment" storage/logs/laravel.log -A 5
```

### RPC Transaction Confirmation
```bash
# In monero-wallet-rpc logs, search for:
curl -X POST http://127.0.0.1:28088/json_rpc \
  -H "Content-Type: application/json" \
  -d '{"method":"get_transfer_by_txid","params":{"txid":"YOUR_TX_HASH"}}'
```

## Known Limitations

1. **Admin Fee Separate Transaction**: Admin fee (3%) is sent as separate transaction after vendor payment. In rare cases, vendor payment succeeds but admin fee fails (logged as error, manual intervention needed).

2. **Fee Estimation**: Monero fees are calculated by daemon automatically. Cannot pre-estimate exact fee for multi-address transactions (varies by number of inputs).

3. **Address Balance Cache**: Database `balance` column may be stale. Always use RPC for spending decisions (which we now do in `findAddressesForPayment`).

## Next Steps (Phase 4)

With multi-address logic complete, we can now implement:
- Feature listing Monero payments (`processFeatureMoneroPayment`)
- This will use the same `findAddressesForPayment` + `sweepAddresses` pattern
- Estimated effort: 2-3 hours

## Completion Checklist

- ✅ `getAddressBalance()` method implemented
- ✅ `findAddressesForPayment()` method implemented  
- ✅ `sweepAddresses()` method implemented
- ✅ `fundMoneroEscrow()` updated for multi-address
- ✅ `processDirectMoneroPayment()` updated for multi-address
- ✅ Error handling added for all new code paths
- ✅ Comprehensive logging added
- ✅ No syntax errors (verified with `get_errors`)
- ✅ Follows existing code patterns and conventions
- ✅ Documentation complete

## Deployment Notes

### Pre-Deployment Testing
```bash
# 1. Verify syntax
php artisan about

# 2. Run cleanup to ensure wallet state is good
php artisan monero:cleanup --verify

# 3. Test with small order amounts first
# Create test order with user who has split balances
```

### Rollback Plan
If issues occur:
1. Revert `app/Repositories/MoneroRepository.php` (remove 3 new methods)
2. Revert `app/Services/EscrowService.php` (restore old `fundMoneroEscrow` and `processDirectMoneroPayment`)
3. No database changes needed (schema unchanged)

### Post-Deployment Monitoring
Watch for:
- Increased "multi-address payment" log entries (expected)
- Any "Failed to sweep addresses" errors (investigate immediately)
- Order completion rates (should increase, not decrease)

---

**Phase 3 Status**: ✅ COMPLETE  
**Implemented By**: Copilot  
**Date**: 2026-02-02  
**Files Modified**: 2  
**Lines Added**: ~220  
**Breaking Changes**: None
