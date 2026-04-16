<?php

namespace App\Console\Commands;

use App\Models\XmrWallet;
use App\Repositories\MoneroRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MoneroWarmWallets extends Command
{
    protected $signature = 'monero:warm
                            {--height= : Start height for refresh (defaults to current chain tip)}
                            {--wallet= : Warm a single wallet by name}';

    protected $description = 'Set all Monero wallet heights to chain tip so monero:sync runs fast';

    public function handle(): int
    {
        $rpcUrl      = config('monero.scheme') . '://' . config('monero.host') . ':' . config('monero.port') . '/json_rpc';
        $rpcUser     = config('monero.user', '');
        $rpcPassword = config('monero.password', '');

        $rpc = function (string $method, array $params = [], int $timeout = 30) use ($rpcUrl, $rpcUser, $rpcPassword) {
            $request = Http::timeout($timeout);
            if (!empty($rpcUser)) {
                $request = $request->withDigestAuth($rpcUser, $rpcPassword);
            }
            $response = $request->post($rpcUrl, [
                'jsonrpc' => '2.0',
                'id'      => '0',
                'method'  => $method,
                'params'  => $params,
            ]);
            return $response->json();
        };

        // Resolve start height
        if ($this->option('height')) {
            $startHeight = (int) $this->option('height');
        } else {
            $this->info('Fetching current chain height from monerod...');
            $daemonUrl  = config('monero.scheme') . '://' . config('monero.host') . ':18081/json_rpc';
            $heightResp = Http::timeout(10)->post($daemonUrl, [
                'jsonrpc' => '2.0',
                'id'      => '0',
                'method'  => 'get_block_count',
                'params'  => [],
            ]);
            $startHeight = $heightResp->json('result.count') ?? 0;
            if (!$startHeight) {
                $this->error('Could not get chain height from monerod. Pass --height= manually.');
                return self::FAILURE;
            }
        }

        $this->info("Using start height: {$startHeight}");

        // Resolve wallets to warm
        if ($this->option('wallet')) {
            $wallets = XmrWallet::where('name', $this->option('wallet'))->get();
            if ($wallets->isEmpty()) {
                $this->error("Wallet '{$this->option('wallet')}' not found.");
                return self::FAILURE;
            }
        } else {
            $wallets = XmrWallet::where('is_active', true)->get();
        }

        $this->info("Warming {$wallets->count()} wallet(s)...");
        $bar = $this->output->createProgressBar($wallets->count());
        $bar->start();

        $ok = 0;
        $failed = 0;

        foreach ($wallets as $wallet) {
            try {
                $password = $wallet->getDecryptedPassword();

                // Close any currently open wallet
                $rpc('close_wallet');

                // Open this wallet
                $open = $rpc('open_wallet', [
                    'filename' => $wallet->name,
                    'password' => $password,
                ]);

                if (isset($open['error'])) {
                    throw new \RuntimeException($open['error']['message'] ?? 'open_wallet failed');
                }

                // Refresh from chain tip — scans no history, just sets internal height
                $rpc('refresh', ['start_height' => $startHeight], 60);

                // Persist and close
                $rpc('store');
                $rpc('close_wallet');

                // Update DB height so monero:sync starts from here
                $wallet->update(['height' => $startHeight]);

                $ok++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->warn("Failed to warm {$wallet->name}: {$e->getMessage()}");
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Ensure no wallet is left open
        $rpc('close_wallet');

        $this->info("Done. {$ok} warmed, {$failed} failed.");
        $this->info("You can now run: php artisan monero:sync");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
