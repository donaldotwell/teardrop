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
    protected $description = 'Mark all current Monero addresses as used and generate new addresses for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Monero Address Regeneration Tool');
        $this->info('This command will:');
        $this->info('1. Mark all unused Monero addresses as used');
        $this->info('2. Generate new receiving addresses for all users');

        if ($this->option('reset-balances')) {
            $this->warn('3. RESET ALL WALLET BALANCES TO ZERO');
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
            // Get all users with Monero wallets
            $users = User::whereHas('xmrWallet')->with('xmrWallet')->get();
            $this->info("Found {$users->count()} users with Monero wallets.");
        }

        if ($users->isEmpty()) {
            $this->warn('No users found with Monero wallets.');
            return 0;
        }

        // Confirmation prompt
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to proceed with address regeneration?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->newLine();
        $this->info('Starting address regeneration...');
        $this->newLine();

        $successCount = 0;
        $failureCount = 0;
        $skippedCount = 0;

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            try {
                DB::beginTransaction();

                // Get or create Monero wallet for user
                $xmrWallet = MoneroRepository::getOrCreateWalletForUser($user);

                // Check if user already has unused addresses
                $currentAddress = $xmrWallet->getCurrentAddress();

                if ($currentAddress) {
                    // Mark all unused addresses as used
                    $markedCount = $xmrWallet->addresses()
                        ->where('is_used', false)
                        ->update(['is_used' => true]);

                    $this->info("Marked {$markedCount} unused addresses as used for user {$user->username_pub}");
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
                $this->error("Failed to regenerate addresses for user {$user->username_pub}: {$e->getMessage()}");
                $failureCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Address regeneration completed:");
        $this->info("✓ Success: {$successCount} users");
        $this->info("✗ Failed: {$failureCount} users");
        $this->info("⊘ Skipped: {$skippedCount} users");

        if ($failureCount > 0) {
            $this->warn('Some users failed to regenerate addresses. Check the logs above for details.');
            return 1;
        }

        $this->info('All Monero addresses have been successfully regenerated.');
        return 0;
    }
}