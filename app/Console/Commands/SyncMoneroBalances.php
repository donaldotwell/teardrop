<?php

namespace App\Console\Commands;

use App\Models\XmrWallet;
use App\Models\User;
use Illuminate\Console\Command;

class SyncMoneroBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monero:sync-balances
                            {--user= : Sync balance for specific user ID only}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Monero wallet balances from RPC for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Monero Balance Sync');
        $this->newLine();

        // Check if syncing for specific user
        $userId = $this->option('user');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }

            if (!$user->xmrWallet) {
                $this->error("User {$user->username_pub} does not have a Monero wallet.");
                return 1;
            }

            $wallets = collect([$user->xmrWallet]);
            $this->info("Targeting specific user: {$user->username_pub} (ID: {$user->id})");
        } else {
            // Get all Monero wallets
            $wallets = XmrWallet::with('user')->get();
            $this->info("Found {$wallets->count()} Monero wallets to sync.");
        }

        if ($wallets->isEmpty()) {
            $this->warn('No Monero wallets found.');
            return 0;
        }

        // Confirmation prompt
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to proceed with balance sync?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->newLine();
        $this->info('Starting balance sync...');
        $this->newLine();

        $successCount = 0;
        $failureCount = 0;

        $progressBar = $this->output->createProgressBar($wallets->count());
        $progressBar->start();

        foreach ($wallets as $xmrWallet) {
            try {
                $oldBalance = $xmrWallet->balance;

                // Sync balance from blockchain via RPC
                $xmrWallet->updateBalance();

                $newBalance = $xmrWallet->fresh()->balance;

                $this->newLine();

                if ($oldBalance != $newBalance) {
                    $this->line("User {$xmrWallet->user->username_pub}: Balance updated from {$oldBalance} to {$newBalance} XMR");
                } else {
                    $this->line("User {$xmrWallet->user->username_pub}: Balance unchanged at {$newBalance} XMR");
                }

                $successCount++;

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("User {$xmrWallet->user->username_pub}: Error - {$e->getMessage()}");

                $failureCount++;

                \Log::error("Failed to sync Monero balance for user {$xmrWallet->user_id}", [
                    'user_id' => $xmrWallet->user_id,
                    'username' => $xmrWallet->user->username_pub,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Monero Balance Sync Complete!');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Successful', $successCount],
                ['Failed', $failureCount],
                ['Total Processed', $wallets->count()],
            ]
        );

        if ($failureCount > 0) {
            $this->newLine();
            $this->warn("Check logs for detailed error information on failed syncs.");
        }

        return $failureCount > 0 ? 1 : 0;
    }
}
