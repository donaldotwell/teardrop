<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExchangeRate;

class ExchangeRatesSeeder extends Seeder
{
    /**
     * Seed the exchange rates table with initial values.
     */
    public function run(): void
    {
        $rates = [
            [
                'crypto_name' => 'bitcoin',
                'crypto_shortname' => 'btc',
                'usd_rate' => 100000.00, // 1 BTC = $100,000
            ],
            [
                'crypto_name' => 'monero',
                'crypto_shortname' => 'xmr',
                'usd_rate' => 230.08, // 1 XMR = $230.08
            ],
        ];

        foreach ($rates as $rate) {
            ExchangeRate::updateOrCreate(
                ['crypto_shortname' => $rate['crypto_shortname']],
                $rate
            );
        }

        $this->command->info('Exchange rates seeded successfully.');
    }
}
