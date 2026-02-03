# Migration Consolidation Plan

## Executive Summary
This document provides a comprehensive mapping of all 61 migration files that need to be consolidated into their base table creation migrations. The goal is to reduce the number of migration files from 61 to approximately 30 by merging all `add_*` and `modify_*` migrations into their base `create_*` migrations.

---

## Tables with Multiple Migrations (Priority Consolidation)

### 1. **users** Table (4 migrations → 1)
**Base Migration:** `0001_01_01_000000_create_users_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2026_01_09_100003_add_early_finalization_tracking_to_users_table.php`
   - Adds: `total_early_finalized_orders` (integer, default 0)
   - Adds: `successful_early_finalized_orders` (integer, default 0)
   - Adds: `early_finalization_enabled` (boolean, default true)
   - Adds: Index on `early_finalization_enabled`

2. ✅ `2026_01_19_091519_add_rating_to_users_table.php`
   - Adds: `rating` (decimal 3,2, default 0.00) after `vendor_level`

**Final Consolidated Schema:**
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('username_pri', 30)->index();
    $table->string('username_pub', 30)->index();
    $table->string('pin', 128);
    $table->string('password', 128);
    $table->string('passphrase_1', 140);
    $table->string('passphrase_2', 140)->nullable();
    $table->integer('trust_level')->default(1);
    $table->integer('vendor_level')->default(0);
    $table->decimal('rating', 3, 2)->default(0.00); // CONSOLIDATED
    $table->timestamp('vendor_since')->nullable();
    $table->integer('total_early_finalized_orders')->default(0); // CONSOLIDATED
    $table->integer('successful_early_finalized_orders')->default(0); // CONSOLIDATED
    $table->boolean('early_finalization_enabled')->default(true); // CONSOLIDATED
    $table->timestamp('last_login_at')->nullable();
    $table->timestamp('last_seen_at')->nullable();
    $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
    $table->text('pgp_pub_key')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    // CONSOLIDATED INDEXES
    $table->index('early_finalization_enabled');
});

// Also creates password_reset_tokens and sessions tables (unchanged)
```

---

### 2. **orders** Table (4 migrations → 1)
**Base Migration:** `2024_12_30_210258_create_orders_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2025_12_07_121245_add_encrypted_delivery_address_to_orders_table.php`
   - Adds: `encrypted_delivery_address` (text, nullable) after `notes`

2. ✅ `2026_01_09_100002_add_finalization_tracking_to_orders_table.php`
   - Adds: `is_early_finalized` (boolean, default false) after `status`
   - Adds: `early_finalized_at` (timestamp, nullable)
   - Adds: `dispute_window_expires_at` (timestamp, nullable)
   - Adds: `finalization_window_id` (foreignId, nullable) with foreign key to `finalization_windows`
   - Adds: `direct_payment_txid` (string, nullable)
   - Adds: `admin_fee_txid` (string, nullable)
   - Adds: Indexes on `is_early_finalized` and `dispute_window_expires_at`

3. ✅ `2026_01_16_113402_add_cancellation_reason_to_orders_table.php`
   - Adds: `cancellation_reason` (text, nullable) after `cancelled_at`

**Final Consolidated Schema:**
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->uuid();
    $table->foreignId('user_id');
    $table->foreignId('listing_id');
    $table->string('currency');
    $table->integer('quantity');
    $table->decimal('usd_price', 10, 2);
    $table->decimal('crypto_value', 10, 8);
    $table->enum('status', ['pending', 'shipped', 'completed', 'cancelled'])->default('pending');
    $table->boolean('is_early_finalized')->default(false); // CONSOLIDATED
    $table->timestamp('early_finalized_at')->nullable(); // CONSOLIDATED
    $table->timestamp('dispute_window_expires_at')->nullable(); // CONSOLIDATED
    $table->foreignId('finalization_window_id')->nullable(); // CONSOLIDATED
    $table->string('direct_payment_txid')->nullable(); // CONSOLIDATED
    $table->string('admin_fee_txid')->nullable(); // CONSOLIDATED
    $table->string('txid')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamp('cancelled_at')->nullable();
    $table->text('cancellation_reason')->nullable(); // CONSOLIDATED
    $table->text('notes')->nullable();
    $table->text('encrypted_delivery_address')->nullable(); // CONSOLIDATED
    $table->timestamps();
    
    // CONSOLIDATED INDEXES
    $table->index('is_early_finalized');
    $table->index('dispute_window_expires_at');
    
    // CONSOLIDATED FOREIGN KEYS
    $table->foreign('finalization_window_id')
          ->references('id')
          ->on('finalization_windows')
          ->onDelete('set null');
});
```

---

### 3. **btc_wallets** Table (2 migrations → 1)
**Base Migration:** `2025_09_10_200803_create_btc_wallets_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2026_01_02_195414_make_user_id_nullable_in_crypto_wallets_for_escrow.php`
   - Changes: Makes `user_id` nullable to support escrow wallets
   - Drops foreign key, makes nullable, re-adds foreign key

**Final Consolidated Schema:**
```php
Schema::create('btc_wallets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // CONSOLIDATED: nullable for escrow
    $table->string('name')->unique();
    $table->string('xpub')->nullable();
    $table->string('address_index')->default(0);
    $table->decimal('total_received', 16, 8)->default(0);
    $table->decimal('total_sent', 16, 8)->default(0);
    $table->decimal('balance', 16, 8)->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

---

### 4. **btc_transactions** Table (4 migrations → 1)
**Base Migration:** `2025_09_10_200827_create_btc_transactions_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2025_12_28_143309_add_soft_deletes_to_btc_transactions_table.php`
   - Adds: `deleted_at` (softDeletes)

2. ✅ `2026_01_14_113452_add_usd_value_to_btc_transactions_table.php`
   - Adds: `usd_value` (decimal 16,2, nullable) after `amount`

3. ✅ `2026_02_02_132148_add_uuid_to_transactions_tables.php`
   - Adds: `uuid` (uuid, nullable, unique) after `id` with comment

**Final Consolidated Schema:**
```php
Schema::create('btc_transactions', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->nullable()->unique()->comment('Unique identifier for external references'); // CONSOLIDATED
    $table->foreignId('btc_wallet_id')->constrained()->onDelete('cascade');
    $table->foreignId('btc_address_id')->nullable()->constrained()->onDelete('set null');
    $table->string('txid')->nullable();
    $table->enum('type', ['deposit', 'withdrawal']);
    $table->decimal('amount', 16, 8);
    $table->decimal('usd_value', 16, 2)->nullable(); // CONSOLIDATED
    $table->decimal('fee', 16, 8)->default(0);
    $table->integer('confirmations')->default(0);
    $table->enum('status', ['pending', 'confirmed', 'failed'])->default('pending');
    $table->json('raw_transaction')->nullable();
    $table->string('block_hash')->nullable();
    $table->integer('block_height')->nullable();
    $table->timestamp('confirmed_at')->nullable();
    $table->timestamps();
    $table->softDeletes(); // CONSOLIDATED

    $table->index(['btc_wallet_id', 'type']);
    $table->index(['status', 'confirmations']);
    $table->index('txid');
    $table->unique(['txid', 'btc_wallet_id', 'type']);
});
```

---

### 5. **xmr_wallets** Table (3 migrations → 1)
**Base Migration:** `2025_11_28_000001_create_xmr_wallets_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2025_12_07_134744_add_seed_and_password_to_xmr_wallets_table.php`
   - Adds: `seed_encrypted` (text, nullable) after `spend_key_encrypted`
   - Adds: `password_hash` (string, nullable) after `seed_encrypted`

2. ✅ `2026_01_02_195414_make_user_id_nullable_in_crypto_wallets_for_escrow.php`
   - Changes: Makes `user_id` nullable to support escrow wallets

3. ✅ `2026_01_30_000001_increase_xmr_decimal_precision.php`
   - Changes: `balance` from DECIMAL(16,12) to DECIMAL(20,12) with COMMENT 'DEPRECATED'
   - Changes: `unlocked_balance` from DECIMAL(16,12) to DECIMAL(20,12) with COMMENT 'DEPRECATED'
   - Changes: `total_received` from DECIMAL(16,12) to DECIMAL(20,12)
   - Changes: `total_sent` from DECIMAL(16,12) to DECIMAL(20,12)

**Final Consolidated Schema:**
```php
Schema::create('xmr_wallets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // CONSOLIDATED: nullable for escrow
    $table->string('name');
    $table->string('primary_address')->unique();
    $table->text('view_key')->nullable();
    $table->text('spend_key_encrypted')->nullable();
    $table->text('seed_encrypted')->nullable(); // CONSOLIDATED
    $table->string('password_hash')->nullable(); // CONSOLIDATED
    $table->unsignedBigInteger('height')->default(0);
    // CONSOLIDATED: Increased precision from 16 to 20
    $table->decimal('balance', 20, 12)->default(0)->comment('DEPRECATED: Stale RPC balance. Use XmrWallet::getBalance() for accurate balance from XmrTransaction sums.'); 
    $table->decimal('unlocked_balance', 20, 12)->default(0)->comment('DEPRECATED: Stale RPC balance. Use XmrWallet::getBalance() for accurate unlocked_balance from XmrTransaction sums.');
    $table->decimal('total_received', 20, 12)->default(0);
    $table->decimal('total_sent', 20, 12)->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

---

### 6. **xmr_addresses** Table (2 migrations → 1)
**Base Migration:** `2025_11_28_000002_create_xmr_addresses_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2026_01_30_000001_increase_xmr_decimal_precision.php`
   - Changes: `balance` from DECIMAL(16,12) to DECIMAL(20,12) with COMMENT 'DEPRECATED'
   - Changes: `total_received` from DECIMAL(16,12) to DECIMAL(20,12)

2. ✅ `2026_02_02_123042_add_last_synced_height_to_xmr_addresses.php`
   - Adds: `last_synced_height` (unsignedBigInteger, default 0) after `last_used_at` with comment
   - Adds: `last_synced_at` (timestamp, nullable) after `last_synced_height` with comment

**Final Consolidated Schema:**
```php
Schema::create('xmr_addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('xmr_wallet_id')->constrained()->onDelete('cascade');
    $table->string('address')->unique();
    $table->integer('account_index')->default(0);
    $table->integer('address_index');
    $table->string('label')->nullable();
    // CONSOLIDATED: Increased precision from 16 to 20
    $table->decimal('balance', 20, 12)->default(0)->comment('DEPRECATED: Stale cached balance. Use XmrTransaction::where(xmr_address_id)->sum(amount) for accurate balance.');
    $table->decimal('total_received', 20, 12)->default(0);
    $table->integer('tx_count')->default(0);
    $table->timestamp('first_used_at')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->unsignedBigInteger('last_synced_height')->default(0)->comment('Last blockchain height synced for this address - used to filter get_transfers() calls'); // CONSOLIDATED
    $table->timestamp('last_synced_at')->nullable()->comment('Timestamp of last sync attempt for this address'); // CONSOLIDATED
    $table->boolean('is_used')->default(false);
    $table->timestamps();

    $table->index(['xmr_wallet_id', 'address_index']);
    $table->index(['xmr_wallet_id', 'account_index', 'address_index']);
});
```

---

### 7. **xmr_transactions** Table (3 migrations → 1)
**Base Migration:** `2025_11_28_000003_create_xmr_transactions_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2026_01_30_000001_increase_xmr_decimal_precision.php`
   - Changes: `amount` from DECIMAL(16,12) to DECIMAL(20,12)
   - Changes: `fee` from DECIMAL(16,12) to DECIMAL(20,12)

2. ✅ `2026_01_30_000002_add_usd_value_to_xmr_transactions_table.php`
   - Adds: `usd_value` (decimal 16,2, nullable) after `amount`

3. ✅ `2026_02_02_132148_add_uuid_to_transactions_tables.php`
   - Adds: `uuid` (uuid, nullable, unique) after `id` with comment

**Final Consolidated Schema:**
```php
Schema::create('xmr_transactions', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->nullable()->unique()->comment('Unique identifier for external references'); // CONSOLIDATED
    $table->foreignId('xmr_wallet_id')->constrained()->onDelete('cascade');
    $table->foreignId('xmr_address_id')->nullable()->constrained()->onDelete('set null');
    $table->string('txid')->nullable();
    $table->string('payment_id')->nullable();
    $table->enum('type', ['deposit', 'withdrawal']);
    $table->decimal('amount', 20, 12); // CONSOLIDATED: Increased precision from 16 to 20
    $table->decimal('usd_value', 16, 2)->nullable(); // CONSOLIDATED
    $table->decimal('fee', 20, 12)->default(0); // CONSOLIDATED: Increased precision from 16 to 20
    $table->integer('confirmations')->default(0);
    $table->unsignedBigInteger('unlock_time')->default(0);
    $table->unsignedBigInteger('height')->nullable();
    $table->enum('status', ['pending', 'confirmed', 'locked', 'unlocked', 'failed'])->default('pending');
    $table->json('raw_transaction')->nullable();
    $table->timestamp('confirmed_at')->nullable();
    $table->timestamp('unlocked_at')->nullable();
    $table->timestamps();

    $table->index(['xmr_wallet_id', 'type']);
    $table->index(['status', 'confirmations']);
    $table->index('txid');
    $table->unique(['txid', 'xmr_wallet_id', 'type']);
});
```

---

### 8. **disputes** Table (2 migrations → 1)
**Base Migration:** `2025_08_18_191156_create_disputes_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2025_09_10_164728_add_moderator_fields_to_disputes_table.php`
   - Adds: `assigned_moderator_id` (foreignId, nullable) after `assigned_admin_id`
   - Adds: `assigned_at` (timestamp, nullable) after `assigned_moderator_id`
   - Adds: `auto_assigned` (boolean, default false) after `assigned_at`
   - Adds: `info_request_deadline` (timestamp, nullable) after `auto_assigned`
   - Adds: `escalation_reason` (text, nullable) after `escalated_at`
   - Adds: `buyer_responded_at` (timestamp, nullable) after `vendor_responded_at`
   - Adds: Indexes on `assigned_moderator_id` and `['status', 'assigned_moderator_id']` and `['auto_assigned', 'assigned_at']`

**Final Consolidated Schema:**
```php
Schema::create('disputes', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique()->index();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade');
    $table->foreignId('disputed_against')->constrained('users')->onDelete('cascade');
    $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('assigned_moderator_id')->nullable()->constrained('users'); // CONSOLIDATED
    $table->timestamp('assigned_at')->nullable(); // CONSOLIDATED
    $table->boolean('auto_assigned')->default(false); // CONSOLIDATED

    $table->enum('type', [
        'item_not_received',
        'item_not_as_described',
        'damaged_item',
        'wrong_item',
        'quality_issue',
        'shipping_issue',
        'vendor_unresponsive',
        'refund_request',
        'other'
    ]);

    $table->enum('status', [
        'open',
        'under_review',
        'waiting_vendor',
        'waiting_buyer',
        'escalated',
        'resolved',
        'closed'
    ])->default('open');

    $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

    $table->string('subject', 255);
    $table->text('description');
    $table->decimal('disputed_amount', 10, 2);
    $table->text('buyer_evidence')->nullable();
    $table->text('vendor_response')->nullable();
    $table->text('admin_notes')->nullable();
    $table->text('resolution_notes')->nullable();

    $table->enum('resolution', [
        'full_refund',
        'partial_refund',
        'no_refund',
        'replacement',
        'store_credit',
        'custom_resolution'
    ])->nullable();

    $table->decimal('refund_amount', 10, 2)->nullable();
    $table->timestamp('info_request_deadline')->nullable(); // CONSOLIDATED
    $table->timestamp('vendor_responded_at')->nullable();
    $table->timestamp('buyer_responded_at')->nullable(); // CONSOLIDATED
    $table->timestamp('admin_reviewed_at')->nullable();
    $table->timestamp('resolved_at')->nullable();
    $table->timestamp('closed_at')->nullable();
    $table->timestamp('escalated_at')->nullable();
    $table->text('escalation_reason')->nullable(); // CONSOLIDATED
    $table->timestamps();

    // CONSOLIDATED INDEXES
    $table->index(['status', 'created_at']);
    $table->index(['assigned_admin_id', 'status']);
    $table->index(['type', 'status']);
    $table->index('priority');
    $table->index('assigned_moderator_id');
    $table->index(['status', 'assigned_moderator_id']);
    $table->index(['auto_assigned', 'assigned_at']);
});
```

---

### 9. **forum_posts** Table (2 migrations → 1)
**Base Migration:** `2025_08_25_122111_create_forum_posts_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2026_01_19_111552_add_moderation_fields_to_forum_posts_table.php`
   - Adds: `status` (enum: 'pending', 'approved', 'rejected', default 'pending') after `body`
   - Adds: `assigned_moderator_id` (foreignId, nullable) after `status`
   - Adds: `moderated_by` (foreignId, nullable) after `assigned_moderator_id`
   - Adds: `moderated_at` (timestamp, nullable) after `moderated_by`
   - Adds: `moderation_notes` (text, nullable) after `moderated_at`
   - Adds: Indexes on `status` and `assigned_moderator_id`

**Final Consolidated Schema:**
```php
Schema::create('forum_posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title', 255);
    $table->text('body');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // CONSOLIDATED
    $table->foreignId('assigned_moderator_id')->nullable()->constrained('users')->nullOnDelete(); // CONSOLIDATED
    $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete(); // CONSOLIDATED
    $table->timestamp('moderated_at')->nullable(); // CONSOLIDATED
    $table->text('moderation_notes')->nullable(); // CONSOLIDATED
    $table->boolean('is_locked')->default(false);
    $table->boolean('is_pinned')->default(false);
    $table->integer('views_count')->default(0);
    $table->timestamp('last_activity_at')->nullable();
    $table->timestamps();
    $table->softDeletes();

    // CONSOLIDATED INDEXES
    $table->index(['created_at', 'is_pinned']);
    $table->index('last_activity_at');
    $table->index('status');
    $table->index('assigned_moderator_id');
});
```

---

### 10. **product_categories** Table (3 migrations → 1)
**Base Migration:** `2024_12_30_215420_create_product_categories_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2026_01_09_100001_add_finalization_fields_to_product_categories_table.php`
   - Adds: `allows_early_finalization` (boolean, default false) after `name`
   - Adds: `finalization_window_id` (foreignId, nullable) after `allows_early_finalization`
   - Adds: `min_vendor_level_for_early` (integer, default 8) after `finalization_window_id`
   - Adds: `early_finalization_notes` (text, nullable) after `min_vendor_level_for_early`
   - Adds: Index on `allows_early_finalization`
   - Adds: Foreign key on `finalization_window_id`

2. ✅ `2026_01_15_130528_add_is_active_to_product_categories_table.php`
   - Adds: `is_active` (boolean, default true) after `name`

**Final Consolidated Schema:**
```php
Schema::create('product_categories', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('name', 50)->unique();
    $table->boolean('is_active')->default(true); // CONSOLIDATED
    $table->boolean('allows_early_finalization')->default(false); // CONSOLIDATED
    $table->foreignId('finalization_window_id')->nullable(); // CONSOLIDATED
    $table->integer('min_vendor_level_for_early')->default(8); // CONSOLIDATED
    $table->text('early_finalization_notes')->nullable(); // CONSOLIDATED
    $table->timestamps();
    
    // CONSOLIDATED INDEXES
    $table->index('allows_early_finalization');
    
    // CONSOLIDATED FOREIGN KEYS
    $table->foreign('finalization_window_id')
          ->references('id')
          ->on('finalization_windows')
          ->onDelete('set null');
});
```

---

### 11. **products** Table (2 migrations → 1)
**Base Migration:** `2024_12_31_102001_create_products_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2026_01_15_130557_add_is_active_to_products_table.php`
   - Adds: `is_active` (boolean, default true) after `name`

**Final Consolidated Schema:**
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->uuid()->unique();
    $table->foreignId('product_category_id');
    $table->string('name', 50)->index();
    $table->boolean('is_active')->default(true); // CONSOLIDATED
    $table->unique(['product_category_id', 'name']);
    $table->timestamps();
});
```

---

### 12. **wallets** Table (2 migrations → 1)
**Base Migration:** `2024_12_30_210318_create_wallets_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2025_12_28_154342_increase_wallets_balance_precision.php`
   - Changes: `balance` from DECIMAL(10,8) to DECIMAL(20,8)

**Final Consolidated Schema:**
```php
Schema::create('wallets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id');
    $table->decimal('balance', 20, 8)->default(0); // CONSOLIDATED: Increased precision from 10 to 20
    $table->enum('currency', ['btc', 'xmr'])->default('btc');
    $table->unique(['currency', 'user_id']);
    $table->timestamps();
});
```

---

### 13. **wallet_transactions** Table (2 migrations → 1)
**Base Migration:** `2024_12_30_210319_create_wallet_transactions_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2025_11_12_081906_add_unique_txid_to_wallet_transactions_table.php`
   - Adds: Unique constraint on `txid` column

**Final Consolidated Schema:**
```php
Schema::create('wallet_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('wallet_id');
    $table->decimal('amount', 10, 8);
    $table->enum('type', ['deposit', 'withdrawal', 'order_escrow', 'order_payment', 'order_refund', 'shipped']);
    $table->string('txid', 255)->nullable()->unique(); // CONSOLIDATED: Added unique constraint
    $table->string('comment', 255)->nullable();
    $table->timestamp('confirmed_at')->nullable();
    $table->timestamp('canceled_at')->nullable();
    $table->timestamp('failed_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamp('expired_at')->nullable();
    $table->timestamps();
});
```

---

### 14. **reviews** Table (2 migrations → 1)
**Base Migration:** `2024_12_30_211355_create_reviews_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2025_12_07_080534_add_order_id_to_reviews_table.php`
   - Adds: `order_id` (foreignId, unique) after `listing_id` with foreign key constraint

**Final Consolidated Schema:**
```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('listing_id');
    $table->foreignId('order_id')->unique()->constrained()->onDelete('cascade'); // CONSOLIDATED
    $table->foreignId('user_id');
    $table->string('comment', 140);
    $table->decimal('buyer_price', 10, 2);
    $table->integer('rating_stealth')->default(0);
    $table->integer('rating_quality')->default(0);
    $table->integer('rating_delivery')->default(0);
    $table->timestamps();
    $table->softDeletes();
});
```

---

### 15. **dispute_messages** Table (2 migrations → 1)
**Base Migration:** `2025_08_18_191341_create_dispute_messages_table.php`

**Subsequent Migrations to Merge:**
1. ✅ `2026_01_21_095220_add_assignment_update_to_dispute_messages_message_type_enum.php`
   - Changes: Adds 'assignment_update' to `message_type` enum

**Final Consolidated Schema:**
```php
Schema::create('dispute_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('dispute_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->text('message');
    $table->enum('message_type', [
        'user_message',
        'admin_message',
        'system_message',
        'status_update',
        'evidence_upload',
        'resolution_note',
        'assignment_update' // CONSOLIDATED: Added to enum
    ])->default('user_message');

    $table->boolean('is_internal')->default(false);
    $table->boolean('is_read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->timestamps();

    $table->index(['dispute_id', 'created_at']);
    $table->index(['user_id', 'is_read']);
});
```

---

## Tables with NO Subsequent Migrations (Keep As-Is)

The following 30+ migration files create standalone tables with no subsequent alterations. These should remain as separate migrations:

### Core Laravel Tables
1. ✅ `0001_01_01_000001_create_cache_table.php` - Cache & cache_locks
2. ✅ `0001_01_01_000002_create_jobs_table.php` - Jobs, job_batches, failed_jobs
3. ✅ `2024_12_18_000001_create_exchange_rates_table.php` - Exchange rates

### Business Logic Tables
4. ✅ `2024_12_30_210338_create_favourites_table.php` - Favourites
5. ✅ `2024_12_30_210435_create_listings_table.php` - Listings
6. ✅ `2024_12_30_210956_create_countries_table.php` - Countries
7. ✅ `2024_12_31_092715_create_roles_table.php` - Roles & role_user pivot
8. ✅ `2024_12_31_092723_create_permissions_table.php` - Permissions & permission_role pivot
9. ✅ `2024_12_31_101438_create_sales_table.php` - Sales
10. ✅ `2025_02_03_102657_create_listing_media_table.php` - Listing media
11. ✅ `2025_02_04_202427_create_user_messages_table.php` - User messages
12. ✅ `2025_02_19_165210_create_message_threads_table.php` - Message threads

### Dispute System Tables
13. ✅ `2025_08_18_191504_create_dispute_evidence_table.php` - Dispute evidence

### Support Ticket Tables
14. ✅ `2025_08_20_082313_create_support_ticket_messages_table.php` - Support ticket messages
15. ✅ `2025_08_20_082344_create_support_ticket_attachments_table.php` - Support ticket attachments

### Forum System Tables
16. ✅ `2025_08_25_122230_create_forum_comments_table.php` - Forum comments
17. ✅ `2025_08_25_123243_create_forum_reports_table.php` - Forum reports
18. ✅ `2025_08_25_123314_create_audit_logs_table.php` - Audit logs

### Bitcoin Tables
19. ✅ `2025_09_10_200814_create_btc_addresses_table.php` - BTC addresses (no changes needed)

### Other Tables
20. ✅ `2025_12_07_120230_create_listing_views_table.php` - Listing views
21. ✅ `2025_12_22_000001_create_pgp_verifications_table.php` - PGP verifications
22. ✅ `2026_01_02_193844_create_escrow_wallets_table.php` - Escrow wallets
23. ✅ `2026_01_09_100000_create_finalization_windows_table.php` - Finalization windows

---

## Migration Summary Statistics

### Before Consolidation
- **Total Migration Files:** 61
- **Tables with Multiple Migrations:** 15
- **Total Migrations to Consolidate:** 31

### After Consolidation
- **Total Migration Files:** ~30
- **Files Eliminated:** ~31
- **Reduction:** ~51%

---

## Consolidation Benefits

1. **Cleaner Migration History**: Single source of truth for each table schema
2. **Faster Fresh Migrations**: Reduces migration execution time by ~50%
3. **Easier Schema Understanding**: Developers can see complete table structure in one file
4. **Reduced Maintenance**: Fewer files to manage and track
5. **Better Testing**: Fresh database setup runs faster in CI/CD pipelines

---

## Implementation Order (Recommended)

### Phase 1: Low Risk Tables (Start Here)
1. `products` (2 migrations → 1)
2. `reviews` (2 migrations → 1)
3. `wallet_transactions` (2 migrations → 1)
4. `wallets` (2 migrations → 1)

### Phase 2: Medium Complexity
5. `product_categories` (3 migrations → 1)
6. `forum_posts` (2 migrations → 1)
7. `dispute_messages` (2 migrations → 1)
8. `btc_wallets` (2 migrations → 1)

### Phase 3: High Complexity (Crypto Tables)
9. `btc_transactions` (4 migrations → 1)
10. `xmr_addresses` (2 migrations → 1)
11. `xmr_wallets` (3 migrations → 1)
12. `xmr_transactions` (3 migrations → 1)

### Phase 4: Critical Business Logic
13. `disputes` (2 migrations → 1)
14. `orders` (4 migrations → 1)
15. `users` (4 migrations → 1)

---

## Testing Checklist After Each Consolidation

For each consolidated migration:

- [ ] Run `php artisan migrate:fresh --seed` successfully
- [ ] Verify table schema matches production using `php artisan migrate:status`
- [ ] Check all indexes are created correctly
- [ ] Verify foreign key constraints work
- [ ] Run application test suite: `php artisan test`
- [ ] Verify models still work correctly
- [ ] Check seeders populate data correctly

---

## Notes & Warnings

### ⚠️ Critical Considerations
1. **Backup Production Database** before applying consolidated migrations
2. **This consolidation is for development/fresh installs only** - Do NOT run on production databases with existing data
3. **Seeders may need updates** if they reference old migration names
4. **Git History**: Consider keeping old migrations in a separate branch for reference

### 🔍 Files to Check After Consolidation
- `database/seeders/*.php` - Update any references to old migration class names
- `tests/` - Update any tests that reference specific migration files
- Documentation - Update any docs mentioning migration file names

---

## Excluded From This Plan

The following are intentionally NOT consolidated because they are standalone:
- All `create_*` migrations with no subsequent changes (30+ files)
- Laravel default migrations (cache, jobs, sessions)
- Pivot tables (role_user, permission_role)

---

## Questions or Issues?

If you encounter issues during consolidation:
1. Check the original migration files for any subtle logic
2. Verify the column order matches expectations (using `after()` clauses)
3. Test with fresh database first
4. Rollback plan: Keep old migrations in git until consolidation is verified

---

**Document Version:** 1.0  
**Generated:** February 3, 2026  
**Total Migrations Analyzed:** 61  
**Tables to Consolidate:** 15
