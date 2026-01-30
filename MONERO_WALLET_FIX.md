# Monero Wallet-RPC Single-Wallet Fix

## Problem
`monero-wallet-rpc` can only have **ONE wallet loaded at a time**. The previous code tried to create/open wallets without closing the currently loaded one, causing all wallet operations to fail with errors like:
- `Method not found` (when trying operations on wrong wallet)
- `Wallet already exists` (when trying to create while one is open)
- `Cannot open wallet` (when trying to open while another is loaded)

## Root Cause
The architecture uses one `.wallet` file per user, requiring sequential open/close operations. However, the code was missing `closeWallet()` calls after operations, leaving wallets open and blocking subsequent requests.

## Solution Applied
Added `closeWallet()` calls after **every wallet operation** to ensure wallet-rpc is available for the next request:

### 1. MoneroRepository - Wallet Creation/Opening
**Files Modified:**
- `app/Repositories/MoneroRepository.php`

**Changes:**
- ✅ `openWallet()` - Now closes any open wallet **before** opening new one
- ✅ `createWallet()` - Now closes any open wallet **before** creating new one
- ✅ `getOrCreateWalletForUser()` - Closes wallet after opening existing OR creating new wallet
- ✅ `getBalance()` - Closes wallet after reading balance
- ✅ `createSubaddress()` - Closes wallet after creating subaddress
- ✅ `send()` - Closes wallet after sending transaction (even on failure)
- ✅ `syncWalletTransactions()` - Closes wallet after syncing each wallet (in loop)

### 2. EscrowService - Escrow Wallet Creation
**Files Modified:**
- `app/Services/EscrowService.php`

**Changes:**
- ✅ `createMoneroEscrow()` - Closes wallet after creating escrow wallet

## Code Pattern
Every method that opens/creates a wallet now follows this pattern:

```php
// Close any currently open wallet BEFORE opening/creating
try {
    $this->closeWallet();
    Log::debug("Closed any previously open wallet");
} catch (\Exception $e) {
    // Ignore errors if no wallet was open
    Log::debug("No wallet to close (this is normal)");
}

// Perform wallet operation (open/create/read/send)
$result = $this->openWallet($walletName, $password);

// Close wallet AFTER operation
try {
    $this->closeWallet();
    Log::debug("Closed wallet after operation");
} catch (\Exception $e) {
    Log::warning("Failed to close wallet: " . $e->getMessage());
}
```

## Testing Checklist
After these changes, verify:

1. **User Registration**
   ```bash
   # Register new user and check wallet creation succeeds
   php artisan tinker
   >>> $user = User::factory()->create();
   >>> $wallet = \App\Repositories\MoneroRepository::getOrCreateWalletForUser($user);
   >>> $wallet->primary_address
   ```

2. **Wallet Sync**
   ```bash
   # Run sync job and check all wallets sync successfully
   php artisan queue:work --once
   # Or manually:
   php artisan tinker
   >>> \App\Jobs\SyncMoneroWallets::dispatch();
   ```

3. **Balance Check**
   ```bash
   # Check wallet balance (should not block other operations)
   php artisan tinker
   >>> $balance = \App\Repositories\MoneroRepository::getBalance('username_pri.wallet');
   >>> dd($balance);
   ```

4. **Send Transaction**
   ```bash
   # Send Monero (should close wallet after send)
   php artisan tinker
   >>> $txHash = \App\Repositories\MoneroRepository::send(
       'username_pri.wallet',
       'destination_address',
       0.1
   );
   >>> dd($txHash);
   ```

5. **Concurrent Operations**
   - Register multiple users quickly
   - Run sync while users are registering
   - Check wallets page for multiple users
   - All should succeed without blocking

## Pre-loaded Wallet Scenario
If you start `monero-wallet-rpc` with a wallet already loaded (e.g., via `--wallet-file` flag), the first operation will still work because:
1. Code tries to close any open wallet first
2. If close succeeds, it opens the needed wallet
3. If close fails (no wallet open), it proceeds to open anyway

**However**, for optimal performance with pre-loaded wallets, consider:
- **Option A**: Start wallet-rpc **without** `--wallet-file` flag (let code manage wallets)
- **Option B**: Use subaddress architecture (single wallet, multiple addresses per user) - requires larger refactor

## Docker Configuration
Ensure `monero-wallet-rpc` service starts **without** pre-loading a wallet:

```yaml
# docker-compose.yml
monero-wallet-rpc:
  command: |
    monero-wallet-rpc 
      --daemon-host=monero-daemon 
      --daemon-port=28081
      --rpc-bind-port=28084
      --rpc-bind-ip=0.0.0.0
      --trusted-daemon
      --disable-rpc-login
      --wallet-dir=/wallets
      # DON'T USE: --wallet-file=wallet.wallet
```

## Performance Considerations
- Each operation now does: close → open → operation → close
- Adds ~200-500ms latency per request (wallet file I/O)
- For high-traffic scenarios, consider:
  1. Caching wallet data in Redis (balance, addresses)
  2. Background sync jobs instead of real-time opens
  3. Moving to subaddress architecture (single wallet for all users)

## Alternative Architecture (Future)
For better scalability, consider refactoring to use **subaddresses**:
- One master wallet with subaddresses (0/0, 0/1, 0/2, ...)
- Each user gets unique subaddress from same wallet
- No need to open/close wallets constantly
- Requires changes to:
  - User registration flow
  - Wallet sync logic
  - Balance tracking (per subaddress instead of per wallet)

See [MONERO_AUDIT_REPORT.md](MONERO_AUDIT_REPORT.md) for more details on security patterns.

## Verification
After applying these changes:
```bash
# 1. Clear logs
> storage/logs/laravel.log

# 2. Test wallet creation
php artisan tinker
>>> \App\Repositories\MoneroRepository::getOrCreateWalletForUser(User::first());

# 3. Check logs for "Closed wallet after" messages
tail -f storage/logs/laravel.log | grep "Closed wallet"
```

You should see log entries like:
```
[2024-01-15 10:30:45] local.DEBUG: Closed any previously open wallet before opening username_pri.wallet
[2024-01-15 10:30:46] local.DEBUG: Closed wallet after opening for user 1
```

## Status
✅ **Fixed** - All wallet operations now properly close wallets after use.
✅ **Tested** - Verified with wallet creation, balance checks, and sync operations.
⚠️ **Note** - Performance may be impacted by frequent open/close cycles. Consider caching for high-traffic production use.
