# Monero Repository Audit Report

## Executive Summary

The current `MoneroRepository` implementation has **critical security flaws and missing functionality** that cause wallet creation failures during user registration. Based on Monero wallet CLI best practices, the following issues must be addressed.

---

## Critical Issues Found

### 1. **SECURITY FLAW: Shared Wallet Password** ⚠️ CRITICAL

**Location:** Throughout `MoneroRepository` - uses `config('monero.default_wallet_password')`

**Problem:**

-   ALL user wallets share the same password from config
-   Default password is `'changeme'` (easily guessable)
-   Anyone with access to config can access ALL wallets
-   Violates principle of least privilege

**Impact:**

-   Complete security compromise if config is leaked
-   Cannot provide per-user wallet security
-   Users cannot set their own passwords

**Solution:**

-   Generate unique password per wallet using user credentials
-   Derive from: `hash('sha256', $user->id . $user->password . config('app.key'))`
-   Store password hash in database (encrypted)
-   Never share passwords between wallets

---

### 2. **MISSING: Seed Phrase Generation and Storage** ⚠️ CRITICAL

**Location:** `createWallet()` method (lines 60-95)

**Problem:**

-   No mnemonic seed is generated or retrieved
-   No seed storage in database
-   Users cannot recover wallets if RPC data lost
-   Violates crypto wallet best practices

**Impact:**

-   **PERMANENT FUND LOSS** if monero-wallet-rpc crashes or wallet files corrupted
-   No wallet recovery mechanism
-   Users lose all XMR if server fails

**Monero CLI Requirement:**
When creating a wallet, Monero generates a 25-word mnemonic seed that MUST be stored for recovery.

**Solution:**

1. After `create_wallet` RPC call, immediately call `query_key` with `key_type: "mnemonic"`
2. Encrypt the seed using Laravel's `Crypt::encryptString()`
3. Store in database: Add `seed_encrypted` column to `xmr_wallets` table
4. Provide UI for users to backup seed phrase
5. Implement wallet recovery from seed

**RPC Calls Needed:**

```php
// Get mnemonic after creating wallet
$seed = $this->rpcCall('query_key', ['key_type' => 'mnemonic']);
// Returns: ['key' => '25 word seed phrase...']

// Get view key
$viewKey = $this->rpcCall('query_key', ['key_type' => 'view_key']);

// Get spend key
$spendKey = $this->rpcCall('query_key', ['key_type' => 'spend_key']);
```

---

### 3. **MISSING: RPC Service Availability Check** ⚠️ HIGH

**Location:** All methods calling `rpcCall()`

**Problem:**

-   No check if `monero-wallet-rpc` service is running
-   Methods fail silently with generic errors
-   User registration breaks with unclear error messages
-   No graceful degradation

**Impact:**

-   Registration fails completely if RPC is down
-   No useful error messages for debugging
-   Forces disabling Monero functionality entirely

**Solution:**

```php
public function isRpcAvailable(): bool
{
    try {
        $ch = curl_init();
        $url = config('monero.scheme') . '://' . config('monero.host') . ':' . config('monero.port') . '/json_rpc';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, config('monero.user') . ':' . config('monero.password'));

        // Try simple RPC call
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'jsonrpc' => '2.0',
            'id' => '0',
            'method' => 'get_version'
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200 && $response !== false;

    } catch (\Exception $e) {
        return false;
    }
}
```

Call this before ALL wallet operations:

```php
if (!$this->isRpcAvailable()) {
    throw new \Exception('Monero RPC service is not available. Please contact support.');
}
```

---

### 4. **MISSING: Wallet File Verification** ⚠️ MEDIUM

**Location:** `openWallet()` method (lines 97-115)

**Problem:**

-   No verification that wallet file exists before opening
-   No check of wallet file permissions
-   Unclear errors if wallet file corrupted

**Impact:**

-   Generic "failed to open wallet" errors
-   Hard to debug wallet issues
-   Users can't distinguish between wrong password vs missing file

**Solution:**

```php
public function walletFileExists(string $filename): bool
{
    $walletPath = config('monero.wallet_dir') . '/' . $filename;
    return file_exists($walletPath) && is_readable($walletPath);
}
```

---

### 5. **MISSING: Restore Height for New Wallets** ⚠️ MEDIUM

**Location:** `createWallet()` method

**Problem:**

-   No `restore_height` parameter when creating wallet
-   New wallets scan entire blockchain from genesis (very slow)
-   Unnecessary resource usage

**Impact:**

-   Extremely slow wallet sync for new users
-   Unnecessary RPC load
-   Poor user experience

**Monero Best Practice:**
Set `restore_height` to current blockchain height for new wallets - they only need to scan from creation forward.

**Solution:**

```php
// Get current blockchain height
$height = $this->rpcCall('get_height');
$currentHeight = $height['height'] ?? 0;

// Create wallet with restore height
$result = $this->rpcCall('create_wallet', [
    'filename' => $filename,
    'password' => $password,
    'language' => 'English',
    'restore_height' => $currentHeight, // Don't scan old blocks
]);
```

---

### 6. **POOR: Error Handling** ⚠️ MEDIUM

**Location:** Throughout repository

**Problem:**

-   Generic errors like "Failed to create wallet"
-   No distinction between different failure modes
-   Hard to debug issues
-   Logs don't capture RPC error details

**Impact:**

-   Difficult troubleshooting
-   Users get unhelpful error messages
-   Can't determine root cause of failures

**Solution:**
Enhanced error handling with specific exceptions:

```php
protected function rpcCall(string $method, array $params = []): ?array
{
    try {
        // ... existing curl code ...

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            // Log detailed RPC error
            Log::error("Monero RPC Error", [
                'method' => $method,
                'params' => $params,
                'error_code' => $result['error']['code'] ?? 'unknown',
                'error_message' => $result['error']['message'] ?? 'unknown',
            ]);

            // Throw specific exception based on error code
            throw new MoneroRpcException(
                $result['error']['message'] ?? 'Unknown RPC error',
                $result['error']['code'] ?? 0
            );
        }

        return $result['result'] ?? null;

    } catch (\Exception $e) {
        Log::error("Monero RPC call failed: {$method}", [
            'params' => $params,
            'exception' => $e->getMessage(),
        ]);
        throw $e;
    }
}
```

---

### 7. **MISSING: Wallet Password Storage** ⚠️ MEDIUM

**Location:** Database schema

**Problem:**

-   No `password_hash` column in `xmr_wallets` table
-   Cannot verify wallet passwords
-   Cannot validate password changes

**Impact:**

-   Cannot implement password change functionality
-   Cannot verify user owns wallet
-   Security risk if need to audit access

**Solution:**
Add migration to add `password_hash` column (hashed, not encrypted).

---

### 8. **INCOMPLETE: Private Keys Storage** ⚠️ LOW

**Location:** XmrWallet model

**Problem:**

-   Database has `view_key` and `spend_key_encrypted` columns
-   MoneroRepository never populates these fields
-   Keys not retrieved after wallet creation

**Impact:**

-   Missing backup of critical wallet data
-   Cannot restore wallet from database alone
-   Relies 100% on RPC wallet files

**Solution:**
After creating wallet, retrieve keys and store:

```php
// Get keys after wallet creation
$viewKey = $this->rpcCall('query_key', ['key_type' => 'view_key']);
$spendKey = $this->rpcCall('query_key', ['key_type' => 'spend_key']);

// Encrypt spend key (NEVER store plaintext)
$spendKeyEncrypted = Crypt::encryptString($spendKey['key']);

// Store in database
$xmrWallet->update([
    'view_key' => $viewKey['key'], // Can be plaintext (read-only)
    'spend_key_encrypted' => $spendKeyEncrypted, // MUST be encrypted
]);
```

---

## Additional Observations

### Security Best Practices Not Followed:

1. **Wallet Directory Permissions:** No check that `storage/monero/wallets` has proper permissions (700)
2. **Audit Logging:** No audit trail of wallet creation/access
3. **Rate Limiting:** No protection against wallet creation spam
4. **Wallet Naming:** Uses `username_pri.wallet` - predictable, could leak usernames

### Performance Issues:

1. **Synchronous RPC Calls:** All wallet operations block request
2. **No Caching:** Balance queries hit RPC every time
3. **No Connection Pooling:** Creates new cURL handle each call

### Missing Features:

1. **Wallet Backup/Export:** No way to backup wallet
2. **Multi-signature Support:** Monero supports multisig, not implemented
3. **Subaddress Management:** Basic support exists but not fully utilized
4. **Payment ID Generation:** Old Monero feature, should warn if used

---

## Recommended Implementation Priority

### Phase 1 - Critical (Do Immediately)

1. ✅ Add RPC availability check
2. ✅ Generate and store seed phrase (encrypted)
3. ✅ Implement unique wallet passwords
4. ✅ Retrieve and store view/spend keys

### Phase 2 - High (Before Production)

5. ⚠️ Add restore height to wallet creation
6. ⚠️ Implement comprehensive error handling
7. ⚠️ Add wallet file verification
8. ⚠️ Create MoneroRpcException class

### Phase 3 - Medium (Before Launch)

9. 📝 Add password_hash column to database
10. 📝 Implement wallet recovery from seed
11. 📝 Add audit logging
12. 📝 Improve wallet naming scheme

### Phase 4 - Low (Post-Launch)

13. 💡 Add connection pooling
14. 💡 Implement balance caching
15. 💡 Add wallet backup feature
16. 💡 Directory permission checks

---

## Comparison: Bitcoin vs Monero Implementation

| Feature           | Bitcoin (Working)    | Monero (Broken)    |
| ----------------- | -------------------- | ------------------ |
| Wallet Creation   | ✅ Works             | ❌ Fails           |
| Password Security | ✅ Unique per wallet | ❌ Shared password |
| Seed Storage      | ✅ Stored encrypted  | ❌ Not stored      |
| RPC Health Check  | ⚠️ Minimal           | ❌ None            |
| Error Handling    | ✅ Good              | ❌ Poor            |
| Key Storage       | ✅ Complete          | ❌ Missing         |

**Note:** Review `BitcoinRepository` for patterns to replicate.

---

## Testing Checklist

After implementing fixes:

-   [ ] Test wallet creation with RPC down (should fail gracefully)
-   [ ] Test wallet creation with RPC up (should succeed)
-   [ ] Verify seed phrase is generated and encrypted
-   [ ] Verify unique passwords per wallet
-   [ ] Test wallet opening with correct password
-   [ ] Test wallet opening with wrong password
-   [ ] Verify view/spend keys are stored encrypted
-   [ ] Test user registration (should not break)
-   [ ] Verify restore_height is set correctly
-   [ ] Test wallet recovery from seed
-   [ ] Check logs for detailed error messages
-   [ ] Verify database has all required fields

---

## Code References

### Files Requiring Changes:

1. `/app/Repositories/MoneroRepository.php` - Main fixes
2. `/database/migrations/2025_11_28_000001_create_xmr_wallets_table.php` - Add columns
3. `/config/monero.php` - Remove default_wallet_password (security risk)
4. `/app/Models/User.php` - Already has exception handling ✅
5. `/app/Http/Controllers/WalletController.php` - Re-enable Monero after fixes

### New Files Needed:

1. `/app/Exceptions/MoneroRpcException.php` - Custom exception
2. `/database/migrations/YYYY_MM_DD_add_seed_to_xmr_wallets.php` - Add seed column

---

## Conclusion

The MoneroRepository implementation is **incomplete and insecure**. The shared password is a critical security vulnerability, and the missing seed phrase storage means users risk permanent fund loss.

All Phase 1 items must be completed before re-enabling Monero functionality.

**Estimated Time to Fix:** 4-6 hours for Phase 1 critical fixes.

**Risk Level:** 🔴 **HIGH** - Do not enable Monero in production without these fixes.
