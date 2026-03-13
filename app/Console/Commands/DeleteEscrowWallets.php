<?php

namespace App\Console\Commands;

use App\Models\EscrowWallet;
use App\Models\BtcWallet;
use App\Models\XmrWallet;
use App\Repositories\BitcoinRepository;
use App\Repositories\MoneroRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class DeleteEscrowWallets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escrow:delete-wallets
                            {--force : Skip confirmation prompt}
                            {--currency= : Delete only BTC or XMR wallets (btc|xmr)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all escrow wallets from database (RPC wallets must be manually deleted)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Escrow Wallet Deletion Tool');
        $this->info('This command will:');
        $this->info('1. Find all escrow wallets in the database');
        $this->info('2. Verify wallet existence on RPC (if available)');
        $this->info('3. Delete EscrowWallet records');
        $this->info('4. Delete associated BtcWallet/XmrWallet records');
        $this->warn('⚠️  RPC wallet files must be manually deleted from the nodes!');

        $this->newLine();

        // Build query for escrow wallets
        $query = EscrowWallet::query();

        if ($this->option('currency')) {
            $currency = strtolower($this->option('currency'));
            if (!in_array($currency, ['btc', 'xmr'])) {
                $this->error('Currency must be either "btc" or "xmr"');
                return 1;
            }
            $query->where('currency', $currency);
            $this->info("Targeting only {$currency} escrow wallets");
        }

        $escrowWallets = $query->with('order')->get();

        if ($escrowWallets->isEmpty()) {
            $this->warn('No escrow wallets found.');
            return 0;
        }

        $this->info("Found {$escrowWallets->count()} escrow wallets:");
        $btcCount = $escrowWallets->where('currency', 'btc')->count();
        $xmrCount = $escrowWallets->where('currency', 'xmr')->count();

        if ($btcCount > 0) {
            $this->info("• {$btcCount} Bitcoin escrow wallets");
        }
        if ($xmrCount > 0) {
            $this->info("• {$xmrCount} Monero escrow wallets");
        }

        $this->newLine();

        // Show sample wallets
        $this->info('Sample escrow wallets:');
        foreach ($escrowWallets->take(5) as $wallet) {
            $orderStatus = $wallet->order ? $wallet->order->status : 'NO ORDER';
            $this->info("• {$wallet->currency}: {$wallet->wallet_name} (Order #{$wallet->order_id}, Status: {$orderStatus})");
        }

        if ($escrowWallets->count() > 5) {
            $this->info("... and " . ($escrowWallets->count() - 5) . " more");
        }

        $this->newLine();

        // Dry run mode
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No wallets will be deleted');
            $this->info('This was a dry run. Use --force to actually delete wallets.');
            return 0;
        }

        // Confirmation prompt
        if (!$this->option('force')) {
            $this->warn('⚠️  WARNING: This will permanently delete escrow wallet records!');
            $this->warn('⚠️  Make sure you have manually deleted the wallet files from RPC nodes first!');
            $this->warn('⚠️  Orders referencing these wallets may break!');

            if (!$this->confirm('Are you absolutely sure you want to delete all escrow wallets?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->newLine();
        $this->info('Starting escrow wallet deletion...');
        $this->newLine();

        $successCount = 0;
        $failureCount = 0;
        $skippedCount = 0;

        $progressBar = $this->output->createProgressBar($escrowWallets->count());
        $progressBar->start();

        foreach ($escrowWallets as $escrowWallet) {
            try {
                DB::beginTransaction();

                // Verify wallet exists on RPC before deletion (if RPC is available)
                $walletExists = $this->verifyWalletExists($escrowWallet);

                if ($walletExists === false) {
                    $this->warn("Wallet {$escrowWallet->wallet_name} not found on RPC, skipping.");
                    $skippedCount++;
                    DB::rollBack();
                    $progressBar->advance();
                    continue;
                }

                // Delete associated wallet record
                if ($escrowWallet->currency === 'btc') {
                    $btcWallet = BtcWallet::where('name', $escrowWallet->wallet_name)->first();
                    if ($btcWallet) {
                        $btcWallet->delete();
                        Log::info("Deleted BtcWallet record", ['wallet_name' => $escrowWallet->wallet_name]);
                    }
                } elseif ($escrowWallet->currency === 'xmr') {
                    $xmrWallet = XmrWallet::where('name', $escrowWallet->wallet_name)->first();
                    if ($xmrWallet) {
                        $xmrWallet->delete();
                        Log::info("Deleted XmrWallet record", ['wallet_name' => $escrowWallet->wallet_name]);
                    }
                }

                // Delete escrow wallet record
                $escrowWallet->delete();

                Log::info("Deleted escrow wallet", [
                    'wallet_name' => $escrowWallet->wallet_name,
                    'currency' => $escrowWallet->currency,
                    'order_id' => $escrowWallet->order_id,
                ]);

                DB::commit();
                $successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to delete escrow wallet {$escrowWallet->wallet_name}: {$e->getMessage()}");
                Log::error("Escrow wallet deletion failed", [
                    'wallet_name' => $escrowWallet->wallet_name,
                    'error' => $e->getMessage(),
                ]);
                $failureCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Escrow wallet deletion completed:");
        $this->info("✓ Success: {$successCount} wallets deleted");
        $this->info("✗ Failed: {$failureCount} wallets");
        $this->info("⊘ Skipped: {$skippedCount} wallets (not found on RPC)");

        if ($failureCount > 0) {
            $this->warn('Some wallets failed to delete. Check the logs for details.');
            return 1;
        }

        $this->warn('⚠️  Remember to manually delete wallet files from RPC nodes!');
        $this->info('Escrow wallet deletion complete.');
        return 0;
    }

    /**
     * Verify if wallet exists on RPC node.
     * Returns true if exists, false if not found, null if RPC unavailable.
     */
    private function verifyWalletExists(EscrowWallet $escrowWallet): ?bool
    {
        try {
            if ($escrowWallet->currency === 'btc') {
                $repository = new BitcoinRepository();

                // For Bitcoin, try to get wallet info - if RPC is down, this will fail
                try {
                    $info = $repository->getWalletInfo($escrowWallet->wallet_name);
                    return !empty($info); // If we get info, wallet exists
                } catch (\Exception $e) {
                    // RPC is unavailable or wallet doesn't exist
                    $this->warn("Bitcoin RPC unavailable or wallet not found for {$escrowWallet->wallet_name}: {$e->getMessage()}");
                    return null;
                }

            } elseif ($escrowWallet->currency === 'xmr') {
                $repository = new MoneroRepository();

                if (!$repository->isRpcAvailable()) {
                    $this->warn("Monero RPC unavailable, skipping verification for {$escrowWallet->wallet_name}");
                    return null;
                }

                // For Monero, try to get wallet info via RPC
                // If the wallet doesn't exist, this will fail
                try {
                    $result = $repository->rpcCall('get_wallet_info');
                    return true; // If we get here, wallet exists and is open
                } catch (\Exception $e) {
                    // Try to open the wallet first
                    try {
                        $password = $escrowWallet->wallet_password_encrypted
                            ? Crypt::decryptString($escrowWallet->wallet_password_encrypted)
                            : MoneroRepository::generateWalletPassword('escrow_order_' . $escrowWallet->order_id);

                        $repository->rpcCall('open_wallet', [
                            'filename' => $escrowWallet->wallet_name,
                            'password' => $password,
                        ]);

                        // If successful, wallet exists
                        $repository->rpcCall('close_wallet');
                        return true;

                    } catch (\Exception $openException) {
                        // Wallet doesn't exist or can't be opened
                        return false;
                    }
                }
            }

        } catch (\Exception $e) {
            $this->warn("Failed to verify wallet {$escrowWallet->wallet_name} existence: {$e->getMessage()}");
            return null;
        }

        return null;
    }
}
