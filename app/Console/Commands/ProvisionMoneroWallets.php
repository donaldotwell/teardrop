<?php

namespace App\Console\Commands;

use App\Models\XmrWallet;
use App\Repositories\MoneroRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProvisionMoneroWallets extends Command
{
    protected $signature = 'monero:provision-wallets
                            {--user= : Provision wallet for a specific user ID only}
                            {--dry-run : Show what would be done without making changes}';

    protected $description = 'Ensure every user XMR wallet record has a corresponding wallet file on the Monero RPC (creates or re-opens missing wallets)';

    public function handle(): int
    {
        $this->info('Monero Wallet Provisioning');
        $this->newLine();

        $repository = new MoneroRepository();

        if (!$repository->isRpcAvailable()) {
            $this->error('Monero RPC is not available. Is monero-wallet-rpc running?');
            return self::FAILURE;
        }

        $this->info('Monero RPC is available.');
        $this->newLine();

        $query = XmrWallet::with('user')->where('is_active', true);
        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }

        $wallets = $query->get();
        $this->info("Found {$wallets->count()} active XMR wallet record(s).");
        $this->newLine();

        $dryRun   = $this->option('dry-run');
        $ok       = 0;
        $skipped  = 0;
        $failed   = 0;

        $bar = $this->output->createProgressBar($wallets->count());
        $bar->start();

        foreach ($wallets as $wallet) {
            $bar->advance();
            $this->newLine();

            $label = $wallet->user?->username_pub ?? "wallet#{$wallet->id}";

            if (!$wallet->user) {
                $this->warn("  {$label}: no user record, skipping.");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->warn("  {$label} ({$wallet->name}): would provision (dry-run).");
                continue;
            }

            try {
                // Password is deterministic — same formula used during wallet creation
                $password = MoneroRepository::generateWalletPassword('user_' . $wallet->user_id);

                // createWalletFile opens if already exists (error -21 → open_wallet)
                $walletData = $repository->createWalletFile($wallet->name, $password);

                // If the stored primary address differs (shouldn't happen), log it
                if ($wallet->primary_address && $wallet->primary_address !== $walletData['address']) {
                    $this->warn("  {$label} ({$wallet->name}): address mismatch — DB={$wallet->primary_address}, RPC={$walletData['address']}");
                    Log::warning("Monero wallet address mismatch on provision", [
                        'user_id'    => $wallet->user_id,
                        'wallet'     => $wallet->name,
                        'db_address' => $wallet->primary_address,
                        'rpc_address' => $walletData['address'],
                    ]);
                } else {
                    $this->info("  {$label} ({$wallet->name}): OK — {$walletData['address']}");
                }

                // Ensure at least one address record exists in DB
                if ($wallet->addresses()->count() === 0) {
                    $wallet->addresses()->create([
                        'address'       => $walletData['address'],
                        'account_index' => 0,
                        'address_index' => 0,
                        'label'         => "User {$wallet->user_id} - primary",
                        'balance'       => 0,
                        'total_received' => 0,
                        'tx_count'      => 0,
                        'is_used'       => false,
                    ]);
                    $this->line("    Created missing address record in DB.");
                }

                Log::info("Monero wallet provisioned", ['user_id' => $wallet->user_id, 'wallet' => $wallet->name]);
                $ok++;

            } catch (\Exception $e) {
                $this->error("  {$label} ({$wallet->name}): failed — {$e->getMessage()}");
                Log::error("Monero wallet provision failed", [
                    'user_id' => $wallet->user_id,
                    'wallet'  => $wallet->name,
                    'error'   => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Done.');
        $this->table(
            ['Status', 'Count'],
            [
                ['Provisioned', $ok],
                ['Skipped',     $skipped],
                ['Failed',      $failed],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
