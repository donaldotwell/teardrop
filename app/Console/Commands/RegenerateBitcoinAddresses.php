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
    protected $description = 'Mark all current Bitcoin addresses as used and generate new addresses for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bitcoin Address Regeneration Tool');
        $this->info('This command will:');
        $this->info('1. Mark all unused Bitcoin addresses as used');
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
            // Get all users with Bitcoin wallets
            $users = User::whereHas('btcWallet')->with('btcWallet')->get();
            $this->info("Found {$users->count()} users with Bitcoin wallets.");
        }

        if ($users->isEmpty()) {
            $this->warn('No users found with Bitcoin wallets.');
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

                // Get or create Bitcoin wallet for user
                $btcWallet = BitcoinRepository::getOrCreateWalletForUser($user);

                // Check if user already has unused addresses
                $currentAddress = $btcWallet->getCurrentAddress();

                if ($currentAddress) {
                    // Mark all unused addresses as used
                    $markedCount = $btcWallet->addresses()
                        ->where('is_used', false)
                        ->update(['is_used' => true]);

                    $this->newLine();
                    $this->line("User {$user->username_pub}: Marked {$markedCount} address(es) as used");
                }

                // Generate new address
                $newAddress = $btcWallet->generateNewAddress();

                if ($newAddress) {
                    $this->line("User {$user->username_pub}: Generated new address {$newAddress->address}");

                    // Reset balances if flag is set
                    if ($this->option('reset-balances')) {
                        $user->fundWallets();
                        $this->line("User {$user->username_pub}: Wallet balances reset to zero");
                    }

                    $successCount++;
                } else {
                    $this->warn("User {$user->username_pub}: Failed to generate new address");
                    $failureCount++;
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();

                $this->newLine();
                $this->error("User {$user->username_pub}: Error - {$e->getMessage()}");
                $this->line("Trace: {$e->getFile()}:{$e->getLine()}");

                $failureCount++;

                \Log::error("Failed to regenerate Bitcoin address for user {$user->id}", [
                    'user_id' => $user->id,
                    'username' => $user->username_pub,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Address Regeneration Complete!');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Successful', $successCount],
                ['Failed', $failureCount],
                ['Total Processed', $users->count()],
            ]
        );

        if ($failureCount > 0) {
            $this->newLine();
            $this->warn("Check logs for detailed error information on failed regenerations.");
        }

        return $failureCount > 0 ? 1 : 0;
    }
}
