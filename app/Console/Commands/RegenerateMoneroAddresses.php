<?php

namespace App\Console\Commands;

use App\Models\XmrWallet;
use App\Models\User;
use App\Repositories\MoneroRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RegenerateMoneroAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monero:regenerate-addresses
                            {--force : Skip confirmation prompt}
                            {--user= : Regenerate for specific user ID only}
                            {--reset-balances : Reset all wallet balances to zero}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark all current Monero addresses as used and generate new addresses for all users (creates wallets if they don\'t exist)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Monero Address Regeneration Tool');
        $this->info('This command will:');
        $this->info('1. Create Monero wallets for users who don\'t have them');
        $this->info('2. Mark all unused Monero addresses as used');
        $this->info('3. Generate new receiving addresses for all users');

        if ($this->option('reset-balances')) {
            $this->warn('4. RESET ALL WALLET BALANCES TO ZERO');
        }

        $this->newLine();

        // Check if regenerating for specific user
        $userId = $this->option('user');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
            $users = collect([$user]);
            $this->info("Targeting specific user: {$user->username_pub} (ID: {$user->id})");
        } else {
            // Get ALL users (not just those with wallets)
            $users = User::all();
            $this->info("Found {$users->count()} total users.");
        }

        if ($users->isEmpty()) {
            $this->warn('No users found.');
            return 0;
        }

        // Count users with existing wallets
        $existingWalletCount = $users->filter(fn($user) => $user->xmrWallet)->count();
        $newWalletCount = $users->count() - $existingWalletCount;

        if ($newWalletCount > 0) {
            $this->info("Will create {$newWalletCount} new Monero wallets.");
        }
        if ($existingWalletCount > 0) {
            $this->info("Will regenerate addresses for {$existingWalletCount} existing wallets.");
        }

        // Confirmation prompt
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to proceed with wallet creation/regeneration?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->newLine();
        $this->info('Starting wallet creation/regeneration...');
        $this->newLine();

        $successCount = 0;
        $failureCount = 0;
        $createdWallets = 0;
        $regeneratedAddresses = 0;

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            try {
                DB::beginTransaction();

                // Get or create Monero wallet for user (this will create if it doesn't exist)
                $hadWallet = $user->xmrWallet !== null;
                $xmrWallet = MoneroRepository::getOrCreateWalletForUser($user);
                $hasWalletNow = true;

                if (!$hadWallet && $hasWalletNow) {
                    $createdWallets++;
                    $this->info("Created new wallet for user {$user->username_pub}");
                }

                // Check if user has unused addresses to mark as used
                $currentAddress = $xmrWallet->getCurrentAddress();

                if ($currentAddress) {
                    // Mark all unused addresses as used
                    $markedCount = $xmrWallet->addresses()
                        ->where('is_used', false)
                        ->update(['is_used' => true]);

                    if ($markedCount > 0) {
                        $regeneratedAddresses++;
                        $this->info("Marked {$markedCount} unused addresses as used for user {$user->username_pub}");
                    }
                }

                // Generate new address
                $newAddress = $xmrWallet->generateNewAddress();

                if ($this->option('reset-balances')) {
                    // Reset wallet balance to zero
                    $xmrWallet->update([
                        'balance' => 0,
                        'unlocked_balance' => 0,
                    ]);

                    // Log balance reset
                    $this->info("Reset balance for user {$user->username_pub}");
                }

                DB::commit();

                $successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to process user {$user->username_pub}: {$e->getMessage()}");
                $failureCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Wallet creation/regeneration completed:");
        $this->info("✓ Success: {$successCount} users processed");
        $this->info("✓ New wallets created: {$createdWallets}");
        $this->info("✓ Addresses regenerated: {$regeneratedAddresses}");
        $this->info("✗ Failed: {$failureCount} users");

        if ($failureCount > 0) {
            $this->warn('Some users failed processing. Check the logs above for details.');
            return 1;
        }

        $this->info('All Monero wallets and addresses have been successfully processed.');
        return 0;
    }
}