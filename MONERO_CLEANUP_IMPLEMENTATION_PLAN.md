# Monero System Audit & Cleanup Implementation Plan

## Executive Summary
After thorough analysis of the Monero implementation, several critical architectural flaws have been identified that affect balance tracking, payment processing, and overall system reliability. This document outlines the issues and provides a comprehensive implementation plan.

---

## Critical Issues Identified

### 1. **Balance Tracking Architecture Flaw**

**Problem:**
- `User::getBalance()` queries `XmrWallet::balance` directly
- `XmrWallet::balance` is updated via RPC calls (expensive, slow, unreliable)
- The comment says "should sum transactions" but implementation queries RPC
- No efficient way to check balances during transactions

**Impact:**
- Slow page loads (RPC call for every page render via AppServiceProvider)
- Race conditions in payment processing
- Inconsistent balance display
- System can't scale (RPC bottleneck)

**Root Cause:**
```php
// User.php line 184
public function getBalance(): array
{
    // Should sum XmrTransaction records but currently reads xmrWallet->balance
    // which is synced from RPC (slow, unreliable)
    return [
        'xmr' => [
            'balance' => $xmrWallet->balance ?? 0, // ❌ WRONG
        ],
    ];
}
```

**Correct Architecture:**
```php
public function getBalance(): array
{
    // Sum confirmed/unlocked transactions in XmrTransaction table
    $xmrBalance = XmrTransaction::where('xmr_wallet_id', $this->xmrWallet->id)
        ->where('status', 'unlocked')
        ->sum(DB::raw('CASE WHEN type = "deposit" THEN amount ELSE -amount END'));
    
    return [
        'xmr' => [
            'balance' => $xmrBalance,
        ],
    ];
}
```

---

### 2. **Multi-Address Payment Logic Missing**

**Problem:**
- `OrderController::store()` checks if user has enough XMR balance
- Attempts to pay from single address (doesn't implement multi-address spending)
- For Monero, funds are distributed across multiple addresses per wallet
- Current code will fail if no single address has full amount

**Location:** `OrderController.php` line ~400-450
```php
// Current code checks total balance but doesn't implement:
// 1. Finding all addresses with funds
// 2. Creating multi-input transactions
// 3. Sweeping funds across addresses
```

**Impact:**
- Orders fail even when user has sufficient total balance
- User confusion ("I have enough XMR, why can't I buy?")
- Requires manual intervention

---

### 3. **Feature Listing Payment Incomplete**

**Problem:**
- `VendorListingController::featureListing()` has `processFeatureMoneroPayment()` method
- Method is **NOT IMPLEMENTED** (only stub exists)
- Vendors cannot feature listings with XMR
- Bitcoin implementation exists but Monero missing

**Location:** `VendorListingController.php` line ~350+

**Impact:**
- Feature discrepancy (BTC works, XMR doesn't)
- Lost revenue potential
- User frustration

---

### 4. **Address Balance Sync Issues**

**Problem:**
- Some XmrAddress records may have stale balances
- No systematic way to sync all addresses
- No command to rebuild balances from blockchain
- Users may not have addresses created yet

**Impact:**
- Incorrect balance displays
- Failed transactions
- Manual database fixes required

---

### 5. **Escrow Release Logic Gap**

**Problem:**
- `OrderController::complete()` calls `EscrowService::releaseEscrow()`
- For Monero, release requires:
  1. Finding vendor's receive address (or creating one)
  2. Transferring from escrow subaddress to vendor subaddress
  3. Calculating admin fee (3%)
  4. Creating admin transaction
  5. Updating all balances

**Current State:**
- Bitcoin escrow release is fully implemented
- Monero escrow release **partial** (doesn't handle multi-address scenarios)

---

## Implementation Plan

### Phase 1: Balance Architecture Fix (CRITICAL - DO FIRST)

#### 1.1 Update `User::getBalance()` Method
**File:** `app/Models/User.php`

```php
public function getBalance(): array
{
    $btcBalance = 0;
    $xmrBalance = 0;

    // Bitcoin: Sum BtcTransaction records
    if ($this->btcWallet) {
        $btcBalance = \App\Models\BtcTransaction::where('btc_wallet_id', $this->btcWallet->id)
            ->where('status', 'confirmed')
            ->sum(DB::raw('CASE WHEN type = "deposit" THEN amount ELSE -amount END'));
    }

    // Monero: Sum XmrTransaction records
    if ($this->xmrWallet) {
        $xmrBalance = \App\Models\XmrTransaction::where('xmr_wallet_id', $this->xmrWallet->id)
            ->where('status', 'unlocked')
            ->sum(DB::raw('CASE WHEN type = "deposit" THEN amount ELSE -amount END'));
    }

    return [
        'btc' => [
            'balance' => max(0, $btcBalance), // Never show negative
            'usd_value' => convert_crypto_to_usd(max(0, $btcBalance), 'btc'),
        ],
        'xmr' => [
            'balance' => max(0, $xmrBalance),
            'usd_value' => convert_crypto_to_usd(max(0, $xmrBalance), 'xmr'),
        ],
    ];
}
```

**Benefits:**
- ✅ Fast (database query, no RPC)
- ✅ Accurate (based on confirmed transactions)
- ✅ Scalable (indexed table lookups)
- ✅ No external dependencies

---

#### 1.2 Remove `XmrWallet::updateBalance()` RPC Calls
**File:** `app/Models/XmrWallet.php`

Keep method for admin tools, but remove from user-facing flows:
- Remove from `User::getBalance()`  
- Remove from AppServiceProvider
- Only use in sync commands

---

#### 1.3 Add Database Index for Performance
**Migration:**
```php
Schema::table('xmr_transactions', function (Blueprint $table) {
    $table->index(['xmr_wallet_id', 'status', 'type']);
});
```

---

### Phase 2: Cleanup Command Implementation

#### 2.1 Command: `php artisan monero:cleanup`

**File:** `app/Console/Commands/MoneroCleanup.php`

**Features:**
1. **Verify All Users Have XmrWallet**
   - Create missing XmrWallet records
   - Link to master wallet
   
2. **Verify All Wallets Have Addresses**
   - Check if wallet has at least one address
   - Generate address if missing
   
3. **Sync Address Balances from RPC**
   - Query `get_balance` for each address_index
   - Update `XmrAddress::balance` field
   - Mark addresses with transactions as `is_used`
   
4. **Rebuild XmrTransaction Records** (Optional Flag)
   - Query `get_transfers` for each address
   - Compare with database records
   - Add missing transactions
   - Update confirmations

5. **Verify Transaction Sums Match RPC**
   - Sum XmrTransactions per wallet
   - Compare with RPC balance
   - Report discrepancies

**Command Signature:**
```bash
php artisan monero:cleanup 
    {--sync-addresses : Sync all address balances from RPC}
    {--rebuild-transactions : Rebuild transaction history from RPC}
    {--verify : Verify balances match RPC}
    {--user= : Only process specific user ID}
    {--dry-run : Show what would be done without making changes}
```

**Output Example:**
```
Monero System Cleanup
=====================

✓ Checking users have XMR wallets...
  - Found 45 users
  - Missing wallets: 0

✓ Checking wallets have addresses...
  - Wallets needing addresses: 3
  - Generating addresses: vendor1, buyer2, buyer5
  - Created 3 new addresses

✓ Syncing address balances from RPC...
  - Address 12: Balance 0.000000000000 XMR ✓
  - Address 15: Balance 1.500000000000 XMR ✓
  - Address 22: Balance 0.123456789012 XMR ✓
  - Synced 45 addresses

✓ Verifying transaction sums...
  - User 5 (vendor1): DB=1.500 XMR, RPC=1.500 XMR ✓
  - User 8 (buyer2): DB=0.123 XMR, RPC=0.123 XMR ✓
  - All balances verified!

Summary:
--------
Users processed: 45
Wallets verified: 45
Addresses synced: 45
Transactions verified: 156
Discrepancies found: 0

✓ Cleanup complete!
```

---

#### 2.2 Add `MoneroRepository::getAddressBalance()` Method
**File:** `app/Repositories/MoneroRepository.php`

```php
/**
 * Get balance for specific subaddress.
 *
 * @param int $addressIndex
 * @param int $accountIndex
 * @return array|null ['balance' => float, 'unlocked_balance' => float]
 */
public function getAddressBalance(int $addressIndex, int $accountIndex = 0): ?array
{
    try {
        $response = $this->rpcCall('get_balance', [
            'account_index' => $accountIndex,
            'address_indices' => [$addressIndex],
        ]);

        if (!isset($response['per_subaddress'][0])) {
            return null;
        }

        $subaddress = $response['per_subaddress'][0];

        return [
            'balance' => $subaddress['balance'] / 1e12, // Convert from atomic units
            'unlocked_balance' => $subaddress['unlocked_balance'] / 1e12,
            'num_unspent_outputs' => $subaddress['num_unspent_outputs'] ?? 0,
        ];
    } catch (\Exception $e) {
        Log::error("Failed to get Monero address balance", [
            'address_index' => $addressIndex,
            'error' => $e->getMessage(),
        ]);
        return null;
    }
}
```

---

### Phase 3: Multi-Address Payment Implementation

#### 3.1 Add `MoneroRepository::sweepAddresses()` Method

```php
/**
 * Sweep funds from multiple addresses to a single destination.
 * Used when buying/sending requires funds from multiple addresses.
 *
 * @param array $sourceAddressIndices Array of address_index values
 * @param string $destinationAddress Monero address to send to
 * @param float $amount Total amount to send (in XMR, not atomic units)
 * @return array ['txid' => string, 'fee' => float]
 */
public function sweepAddresses(array $sourceAddressIndices, string $destinationAddress, float $amount): array
{
    // Implementation:
    // 1. For each source address, call sweep_single (or transfer)
    // 2. Aggregate results
    // 3. Return combined txid and total fees
    // 4. Mark addresses as used
}
```

#### 3.2 Update `OrderController::store()` Payment Logic

Add logic to:
1. Get user's XMR addresses with balances
2. Select addresses until amount covered
3. Call `sweepAddresses()` if multiple addresses needed
4. Create XmrTransaction records for each source address

---

### Phase 4: Feature Listing Monero Payment

#### 4.1 Implement `VendorListingController::processFeatureMoneroPayment()`

```php
private function processFeatureMoneroPayment(Listing $listing, $vendor, $feeUsd)
{
    // Get exchange rate
    $xmrRate = ExchangeRate::where('crypto_shortname', 'xmr')->firstOrFail();
    $requiredAmountXmr = $feeUsd / $xmrRate->usd_rate;

    // Get vendor's XMR wallet
    $vendorXmrWallet = $vendor->xmrWallet;
    if (!$vendorXmrWallet) {
        throw new \Exception('Monero wallet not found.');
    }

    // Check balance (sum transactions)
    $balance = XmrTransaction::where('xmr_wallet_id', $vendorXmrWallet->id)
        ->where('status', 'unlocked')
        ->sum(DB::raw('CASE WHEN type = "deposit" THEN amount ELSE -amount END'));

    if ($balance < $requiredAmountXmr) {
        throw new \Exception('Insufficient Monero balance. Required: ' . 
            number_format($requiredAmountXmr, 12) . ' XMR');
    }

    // Get admin wallet address
    $adminWalletName = config('fees.admin_xmr_wallet_name', 'admin');
    $adminXmrWallet = XmrWallet::where('name', $adminWalletName)->firstOrFail();
    $adminAddress = $adminXmrWallet->getCurrentAddress();

    if (!$adminAddress) {
        $adminAddress = $adminXmrWallet->generateNewAddress();
    }

    // Send Monero to admin (using MoneroRepository helper)
    $repository = new MoneroRepository();
    $txid = $repository->transfer(
        $vendorXmrWallet->addresses()->where('balance', '>', 0)->pluck('address_index')->toArray(),
        $adminAddress->address,
        $requiredAmountXmr
    );

    if (!$txid) {
        throw new \Exception('Failed to send Monero transaction.');
    }

    // Create transaction record
    XmrTransaction::create([
        'xmr_wallet_id' => $vendorXmrWallet->id,
        'xmr_address_id' => null, // Multi-address payment
        'txid' => $txid,
        'type' => 'withdrawal',
        'amount' => $requiredAmountXmr,
        'fee' => 0,
        'confirmations' => 0,
        'status' => 'pending',
        'raw_transaction' => [
            'listing_id' => $listing->id,
            'purpose' => 'feature_listing',
            'fee_usd' => $feeUsd,
            'to_address' => $adminAddress->address,
        ],
    ]);

    // Mark listing as featured
    $listing->update([
        'is_featured' => true,
        'featured_at' => now(),
        'featured_expires_at' => now()->addDays(config('fees.featured_listing_days', 7)),
    ]);

    Log::info("Monero feature listing payment processed", [
        'listing_id' => $listing->id,
        'vendor_id' => $vendor->id,
        'amount_xmr' => $requiredAmountXmr,
        'txid' => $txid,
    ]);
}
```

---

### Phase 5: Escrow Release Enhancement

#### 5.1 Review `EscrowService::releaseMoneroEscrow()`

Ensure it handles:
- Finding/creating vendor receive address
- Calculating 3% admin fee
- Splitting payment (97% vendor, 3% admin)
- Creating XmrTransaction records for both
- Proper error handling

#### 5.2 Add Tests for Escrow Flow

- Test escrow creation
- Test escrow funding
- Test escrow release
- Test admin fee calculation
- Test failed scenarios

---

## Testing Strategy

### Unit Tests

1. **Balance Calculation Tests**
   ```php
   test_user_balance_sums_transactions()
   test_balance_never_negative()
   test_balance_with_no_transactions()
   ```

2. **Multi-Address Payment Tests**
   ```php
   test_sweep_multiple_addresses()
   test_insufficient_funds_across_addresses()
   ```

3. **Cleanup Command Tests**
   ```php
   test_cleanup_creates_missing_wallets()
   test_cleanup_generates_missing_addresses()
   test_cleanup_syncs_balances()
   ```

### Integration Tests

1. **Full Order Flow**
   - Place order with XMR
   - Fund escrow
   - Complete order
   - Verify vendor receives payment
   - Verify admin receives fee

2. **Feature Listing Flow**
   - Vendor features listing with XMR
   - Verify payment sent to admin
   - Verify listing marked as featured

---

## Migration Strategy

### Step 1: Deploy Balance Fix (Zero Downtime)
1. Deploy updated `User::getBalance()` method
2. Add database index
3. Monitor performance improvement

### Step 2: Run Cleanup Command (Maintenance Window)
1. Enable maintenance mode
2. Run `php artisan monero:cleanup --verify`
3. Fix any discrepancies found
4. Re-run with `--sync-addresses`
5. Disable maintenance mode

### Step 3: Deploy Payment Enhancements
1. Deploy multi-address payment logic
2. Deploy feature listing Monero support
3. Test with small amounts first

### Step 4: Monitor & Validate
1. Monitor error logs for 48 hours
2. Check balance discrepancies daily
3. Verify user feedback

---

## Success Metrics

- ✅ All users have XmrWallet records
- ✅ All wallets have at least one address
- ✅ All address balances sync correctly from RPC
- ✅ `User::getBalance()` returns in <100ms (vs. current ~1-2s)
- ✅ Zero balance discrepancies between DB and RPC
- ✅ Orders process successfully with multi-address payments
- ✅ Feature listing works with both BTC and XMR
- ✅ Escrow release completes without errors

---

## Priority Order

1. **CRITICAL - Phase 1** (Balance Architecture) - Deploy ASAP
2. **HIGH - Phase 2** (Cleanup Command) - Run after Phase 1
3. **MEDIUM - Phase 3** (Multi-Address Payment) - Next sprint
4. **LOW - Phase 4** (Feature Listing) - Can be done anytime
5. **LOW - Phase 5** (Escrow Enhancement) - Review after Phase 3

---

## Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| RPC unavailable during cleanup | Command fails | Add retry logic, run during low traffic |
| Balance discrepancies | User confusion | Add --dry-run mode, verify before apply |
| Multi-address payment fails | Order fails | Keep single-address fallback logic |
| Database migration errors | Downtime | Test on staging, have rollback plan |

---

## Next Steps

1. Review this plan with team
2. Create tasks in project management system
3. Assign Phase 1 to developer
4. Schedule Phase 2 cleanup window
5. Begin implementation

---

## Questions to Resolve

1. **Admin Wallet Setup**: Does admin XMR wallet exist? What's the wallet name in config?
2. **Fee Config**: Confirm 3% admin fee is correct for all transactions?
3. **Featured Listing**: How long should listings stay featured? (current: 7 days)
4. **Cleanup Frequency**: Should cleanup command run on cron? How often?
5. **Migration Timing**: When can we schedule maintenance window?

---

## Conclusion

The current Monero implementation has significant architectural issues that prevent proper balance tracking and payment processing. This plan provides a systematic approach to fixing these issues while minimizing user impact and ensuring system reliability.

**Estimated Effort:**
- Phase 1: 4-6 hours
- Phase 2: 6-8 hours  
- Phase 3: 8-12 hours
- Phase 4: 4-6 hours
- Phase 5: 4-6 hours
- **Total: 26-38 hours** (~1 week of focused development)

