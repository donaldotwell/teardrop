<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\ExchangeRate;

class ExchangeRatesUpdater extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update BTC and XMR exchange rates from CoinGecko API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating exchange rates for BTC and XMR...');

        try {
            // Fetch rates from CoinGecko API (free, no API key required)
            $response = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'bitcoin,monero',
                'vs_currencies' => 'usd',
            ]);

            if (!$response->successful()) {
                $this->error('Failed to fetch exchange rates from CoinGecko API.');
                $this->error('Status: ' . $response->status());
                return 1;
            }

            $data = $response->json();

            if (!$data) {
                $this->error('Empty response from CoinGecko API.');
                return 1;
            }

            // Update Bitcoin rate
            if (isset($data['bitcoin']['usd'])) {
                $btcRate = $data['bitcoin']['usd'];
                ExchangeRate::updateOrCreate(
                    ['crypto_shortname' => 'btc'],
                    [
                        'crypto_name' => 'bitcoin',
                        'crypto_shortname' => 'btc',
                        'usd_rate' => $btcRate,
                    ]
                );
                $this->line("✓ BTC rate updated: $" . number_format($btcRate, 2));
            } else {
                $this->warn('Bitcoin rate not found in API response.');
            }

            // Update Monero rate
            if (isset($data['monero']['usd'])) {
                $xmrRate = $data['monero']['usd'];
                ExchangeRate::updateOrCreate(
                    ['crypto_shortname' => 'xmr'],
                    [
                        'crypto_name' => 'monero',
                        'crypto_shortname' => 'xmr',
                        'usd_rate' => $xmrRate,
                    ]
                );
                $this->line("✓ XMR rate updated: $" . number_format($xmrRate, 2));
            } else {
                $this->warn('Monero rate not found in API response.');
            }

            $this->info('Exchange rates updated successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error updating exchange rates: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());

            if ($this->output->isVerbose()) {
                $this->error('Stack trace:');
                $this->line($e->getTraceAsString());
            } else {
                $this->warn('Run with -v flag for full stack trace.');
            }

            return 1;
        }
    }
}
