<?php

namespace App\Console\Commands;

use App\Models\BtcWallet;
use App\Repositories\BitcoinRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProvisionBitcoinWallets extends Command
{
    protected $signature = 'bitcoin:provision-wallets
                            {--user= : Provision wallet for a specific user ID only}
                            {--dry-run : Show what would be done without making changes}';

    protected $description = 'Ensure every user BTC wallet record has a corresponding wallet on the Bitcoin node (creates or loads missing wallets)';

    public function handle(): int
    {
        $this->info('Bitcoin Wallet Provisioning');
        $this->newLine();

        $repository = new BitcoinRepository();

        if (!$repository->isRpcAvailable()) {
            $this->error('Bitcoin RPC is not available. Is bitcoind running?');
            return self::FAILURE;
        }

        $this->info('Bitcoin RPC is available.');
        $this->newLine();

        $query = BtcWallet::with('user')->where('is_active', true);
        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }

        $wallets = $query->get();
        $this->info("Found {$wallets->count()} active BTC wallet record(s).");
        $this->newLine();

        // Fetch all currently-loaded wallets once (cheaper than one RPC per wallet)
        $loadedWallets = $repository->listLoadedWallets();
        if ($loadedWallets === null) {
            $this->error('Could not retrieve loaded wallet list from node.');
            return self::FAILURE;
        }

        $this->info('Loaded wallets on node: ' . (count($loadedWallets) ? implode(', ', $loadedWallets) : '(none)'));
        $this->newLine();

        $dryRun  = $this->option('dry-run');
        $created = 0;
        $loaded  = 0;
        $already = 0;
        $skipped = 0;
        $failed  = 0;

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

            $name = $wallet->name;

            if (in_array($name, $loadedWallets)) {
                $this->line("  {$label} ({$name}): already loaded.");
                $already++;
                continue;
            }

            if ($dryRun) {
                $this->warn("  {$label} ({$name}): would provision (dry-run).");
                continue;
            }

            // Try loading first (wallet file may exist on disk but not be loaded)
            $loadResult = $repository->loadWallet($name);
            if ($loadResult === 'loaded') {
                $this->info("  {$label} ({$name}): wallet file found on disk and loaded.");
                $loaded++;
                $loadedWallets[] = $name;
                continue;
            }

            // Not on disk — create fresh
            $ok = $repository->createWallet($name);
            if ($ok) {
                $this->info("  {$label} ({$name}): wallet created on node.");
                Log::info("Bitcoin wallet provisioned on node", ['user_id' => $wallet->user_id, 'wallet' => $name]);
                $created++;
                $loadedWallets[] = $name;

                // Generate first address if none recorded in DB
                if ($wallet->addresses()->count() === 0) {
                    $address = $repository->getNewAddress($name);
                    if ($address) {
                        $wallet->addresses()->create([
                            'address'       => $address,
                            'address_index' => 0,
                            'is_used'       => false,
                        ]);
                        $this->line("    Generated first address: {$address}");
                    }
                }
            } else {
                $this->error("  {$label} ({$name}): failed to create wallet — check logs.");
                $failed++;
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Done.');
        $this->table(
            ['Status', 'Count'],
            [
                ['Already loaded',  $already],
                ['Loaded from disk', $loaded],
                ['Created (new)',    $created],
                ['Skipped',         $skipped],
                ['Failed',          $failed],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

}
