<?php

namespace App\Console\Commands;

use App\Models\BtcWallet;
use App\Models\User;
use App\Repositories\BitcoinRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RegenerateBitcoinAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitcoin:regenerate-addresses
                            {--force : Skip confirmation prompt}
                            {--user= : Regenerate for specific user ID only}
                            {--reset-balances : Reset all wallet balances to zero}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark all current Bitcoin addresses as used and generate new addresses for all users (creates wallets if they don\'t exist)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bitcoin Address Regeneration Tool');
        $this->info('This command will:');
        $this->info('1. Create Bitcoin wallets for users who don\'t have them');
        $this->info('2. Mark all unused Bitcoin addresses as used (addresses may not exist on disk)');
        $this->info('3. Generate new receiving addresses for all users');
        $this->info('4. Reset all wallet balances to zero (model only)');

        if ($this->option('reset-balances')) {
            $this->warn('4b. (Optional flag no longer required — balances are always reset in the model)');
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
        $existingWalletCount = $users->filter(fn($user) => $user->btcWallet)->count();
        $newWalletCount = $users->count() - $existingWalletCount;

        if ($newWalletCount > 0) {
            $this->info("Will create {$newWalletCount} new Bitcoin wallets.");
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

                // Delete existing wallet and addresses if they exist (addresses will be marked as used on disk, but we want to clear them from DB)
                if ($user->btcWallet) {
                    $user->btcWallet->addresses()->delete();
                    $user->btcWallet()->delete();
                }

                // Get or create Bitcoin wallet for user (this will create if it doesn't exist)
                $hadWallet = $user->btcWallet !== null;
                $btcWallet = BitcoinRepository::getOrCreateWalletForUser($user);
                $hasWalletNow = true;

                if (!$hadWallet && $hasWalletNow) {
                    $createdWallets++;
                    $this->info("Created new wallet for user {$user->username_pub}");
                }

                // Check if user has unused addresses to mark as used
                $currentAddress = $btcWallet->getCurrentAddress();

                // mark all addresses as used regardless (addresses may not exist on disk yet)
                $markedCount = $btcWallet->addresses()
                    ->where('is_used', false)
                    ->update(['is_used' => true]);

                if ($markedCount > 0) {
                    $regeneratedAddresses++;
                    $this->info("Marked {$markedCount} unused addresses as used for user {$user->username_pub}");
                }

                // reset balances on model in every case
                $btcWallet->update([
                    'balance' => 0,
                ]);

                // Generate new address
                $newAddress = $btcWallet->generateNewAddress();

                // (balances are reset above unconditionally, flag is informational only)
                if ($this->option('reset-balances')) {
                    $this->info("Flag --reset-balances provided (balances are reset regardless).");
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

        $this->info('All Bitcoin wallets and addresses have been successfully processed.');
        return 0;
    }
}
