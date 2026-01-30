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
     * Test that wallet operations properly close wallets after use.
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

        // Create wallet (should close after creation)
        Log::shouldReceive('debug')
            ->withArgs(function ($message) use ($user) {
                return str_contains($message, 'Closed wallet after creation for user');
            })
            ->once();

        $wallet = MoneroRepository::getOrCreateWalletForUser($user);

        $this->assertNotNull($wallet);
        $this->assertNotNull($wallet->primary_address);
    }

    /**
     * Test that multiple wallet operations don't block each other.
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

        // Verify they're different wallets
        $this->assertNotEquals($wallet1->primary_address, $wallet2->primary_address);
    }

    /**
     * Test that balance check closes wallet after reading.
     */
    public function test_balance_check_closes_wallet(): void
    {
        $repository = new MoneroRepository();
        if (!$repository->isRpcAvailable()) {
            $this->markTestSkipped('Monero RPC not available');
        }

        // Create user with wallet
        $user = User::factory()->create(['username_pri' => 'testuser_' . uniqid()]);
        $wallet = MoneroRepository::getOrCreateWalletForUser($user);

        // Check balance (should close wallet after check)
        Log::shouldReceive('debug')
            ->withArgs(function ($message) use ($wallet) {
                return str_contains($message, 'Closed wallet after balance check');
            })
            ->once();

        $balance = MoneroRepository::getBalance($wallet->name);

        // Balance check should succeed
        $this->assertIsArray($balance);
        $this->assertArrayHasKey('balance', $balance);
        $this->assertArrayHasKey('unlocked_balance', $balance);

        // Should be able to create another wallet immediately after
        $user2 = User::factory()->create(['username_pri' => 'testuser2_' . uniqid()]);
        $wallet2 = MoneroRepository::getOrCreateWalletForUser($user2);
        $this->assertNotNull($wallet2);
    }

    /**
     * Test that openWallet closes any previously open wallet.
     */
    public function test_open_wallet_closes_previous_wallet(): void
    {
        $repository = new MoneroRepository();
        if (!$repository->isRpcAvailable()) {
            $this->markTestSkipped('Monero RPC not available');
        }

        // Create two users with wallets
        $user1 = User::factory()->create(['username_pri' => 'opentest1_' . uniqid()]);
        $user2 = User::factory()->create(['username_pri' => 'opentest2_' . uniqid()]);

        $wallet1 = MoneroRepository::getOrCreateWalletForUser($user1);
        $wallet2 = MoneroRepository::getOrCreateWalletForUser($user2);

        // Should log that it closed previous wallet before opening
        Log::shouldReceive('debug')
            ->withArgs(function ($message) use ($wallet1) {
                return str_contains($message, 'Closed any previously open wallet before opening');
            })
            ->atLeast()->once();

        // Open first wallet
        $password1 = $repository->generateWalletPassword($user1);
        $opened1 = $repository->openWallet($wallet1->name, $password1);
        $this->assertTrue($opened1);

        // Open second wallet (should close first wallet first)
        $password2 = $repository->generateWalletPassword($user2);
        $opened2 = $repository->openWallet($wallet2->name, $password2);
        $this->assertTrue($opened2);
    }

    /**
     * Test that createWallet closes any previously open wallet.
     */
    public function test_create_wallet_closes_previous_wallet(): void
    {
        $repository = new MoneroRepository();
        if (!$repository->isRpcAvailable()) {
            $this->markTestSkipped('Monero RPC not available');
        }

        // Create first wallet
        $user1 = User::factory()->create(['username_pri' => 'createtest1_' . uniqid()]);
        $wallet1 = MoneroRepository::getOrCreateWalletForUser($user1);

        // Should log that it closes wallet before creating new one
        Log::shouldReceive('debug')
            ->withArgs(function ($message) {
                return str_contains($message, 'Closed any previously open wallet before creating');
            })
            ->atLeast()->once();

        // Create second wallet (should close first if open)
        $user2 = User::factory()->create(['username_pri' => 'createtest2_' . uniqid()]);
        $wallet2 = MoneroRepository::getOrCreateWalletForUser($user2);

        $this->assertNotNull($wallet2);
        $this->assertNotEquals($wallet1->primary_address, $wallet2->primary_address);
    }
}
