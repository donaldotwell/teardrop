<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\MoneroRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MoneroWalletClosingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that wallet creation produces a valid wallet with address.
     */
    public function test_wallet_creation_closes_wallet(): void
    {
        // Skip if Monero RPC not available
        $repository = new MoneroRepository();
        if (!$repository->isRpcAvailable()) {
            $this->markTestSkipped('Monero RPC not available');
        }

        // Create test user
        $user = User::factory()->create([
            'username_pri' => 'testuser' . uniqid(),
        ]);

        // Create wallet (opens wallet file, gets address, closes)
        $wallet = MoneroRepository::getOrCreateWalletForUser($user);

        $this->assertNotNull($wallet);
        $this->assertNotNull($wallet->primary_address);
        $this->assertNotNull($wallet->password_encrypted);
        $this->assertEquals("user_{$user->id}", $wallet->name);

        // Should have at least one address record
        $this->assertGreaterThanOrEqual(1, $wallet->addresses()->count());
    }

    /**
     * Test that multiple wallet operations don't block each other.
     * Each withWallet() call acquires + releases the global lock,
     * so sequential calls must succeed.
     */
    public function test_sequential_wallet_operations_dont_block(): void
    {
        $repository = new MoneroRepository();
        if (!$repository->isRpcAvailable()) {
            $this->markTestSkipped('Monero RPC not available');
        }

        // Create two test users
        $user1 = User::factory()->create(['username_pri' => 'testuser1_' . uniqid()]);
        $user2 = User::factory()->create(['username_pri' => 'testuser2_' . uniqid()]);

        // Create first wallet
        $wallet1 = MoneroRepository::getOrCreateWalletForUser($user1);
        $this->assertNotNull($wallet1);

        // Create second wallet (should succeed because first wallet was closed)
        $wallet2 = MoneroRepository::getOrCreateWalletForUser($user2);
        $this->assertNotNull($wallet2);

        // Verify they're different wallets with different names and addresses
        $this->assertNotEquals($wallet1->name, $wallet2->name);
        $this->assertNotEquals($wallet1->primary_address, $wallet2->primary_address);
    }

    /**
     * Test that balance check works via per-wallet open/close lifecycle.
     */
    public function test_balance_check_works(): void
    {
        $repository = new MoneroRepository();
        if (!$repository->isRpcAvailable()) {
            $this->markTestSkipped('Monero RPC not available');
        }

        // Create user with wallet
        $user = User::factory()->create(['username_pri' => 'testuser_' . uniqid()]);
        $wallet = MoneroRepository::getOrCreateWalletForUser($user);

        // Check balance via instance method (opens wallet, gets balance, closes)
        $balance = $repository->getWalletBalance($wallet);

        // Balance check should succeed and return expected keys
        $this->assertIsArray($balance);
        $this->assertArrayHasKey('balance', $balance);
        $this->assertArrayHasKey('unlocked_balance', $balance);

        // New wallet should have zero balance
        $this->assertEquals(0.0, $balance['balance']);
        $this->assertEquals(0.0, $balance['unlocked_balance']);

        // Should be able to create another wallet immediately after
        $user2 = User::factory()->create(['username_pri' => 'testuser2_' . uniqid()]);
        $wallet2 = MoneroRepository::getOrCreateWalletForUser($user2);
        $this->assertNotNull($wallet2);
    }

    /**
     * Test that withWallet lifecycle correctly opens and closes wallets.
     */
    public function test_with_wallet_lifecycle(): void
    {
        $repository = new MoneroRepository();
        if (!$repository->isRpcAvailable()) {
            $this->markTestSkipped('Monero RPC not available');
        }

        $user = User::factory()->create(['username_pri' => 'lifecycletest_' . uniqid()]);
        $wallet = MoneroRepository::getOrCreateWalletForUser($user);

        $password = $wallet->getDecryptedPassword();

        // Use withWallet to run a callback inside the open wallet
        $result = $repository->withWallet($wallet->name, $password, function () use ($repository) {
            // Inside callback, wallet is open — get balance
            return $repository->getOpenWalletBalance();
        });

        $this->assertIsArray($result);
        $this->assertArrayHasKey('balance', $result);
        $this->assertArrayHasKey('unlocked_balance', $result);

        // After withWallet returns, lock is released — another operation should work
        $user2 = User::factory()->create(['username_pri' => 'lifecycletest2_' . uniqid()]);
        $wallet2 = MoneroRepository::getOrCreateWalletForUser($user2);
        $this->assertNotNull($wallet2);
    }

    /**
     * Test that wallet password is deterministic and decryptable.
     */
    public function test_wallet_password_deterministic(): void
    {
        $repository = new MoneroRepository();
        if (!$repository->isRpcAvailable()) {
            $this->markTestSkipped('Monero RPC not available');
        }

        $user = User::factory()->create(['username_pri' => 'pwdtest_' . uniqid()]);
        $wallet = MoneroRepository::getOrCreateWalletForUser($user);

        // The generated password should match what generateWalletPassword produces
        $expectedPassword = MoneroRepository::generateWalletPassword("user_{$user->id}");
        $decrypted = $wallet->getDecryptedPassword();

        $this->assertEquals($expectedPassword, $decrypted);
    }
}
