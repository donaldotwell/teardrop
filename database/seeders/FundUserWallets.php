<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FundUserWallets extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $starterFunds = [
                'btc' => 10.01, // Set starter balance for BTC
                'xmr' => 9.5   // Set starter balance for XMR
            ];

            User::chunk(100, function ($users) use ($starterFunds) {
                foreach ($users as $user) {
                    foreach ($starterFunds as $currency => $amount) {
                        $wallet = Wallet::firstOrCreate([
                            'user_id'  => $user->id,
                            'currency' => $currency,
                        ], [
                            'balance' => 0,
                        ]);

                        // Fund the wallet
                        $wallet->balance += $amount;
                        $wallet->save();

                        // Create a transaction record
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'amount'    => $amount,
                            'type'      => 'deposit',
                            'txid'      => null,
                            'comment'   => 'Initial funding',
                            'confirmed_at' => now(),
                            'completed_at' => now(),
                        ]);
                    }
                }
            });
        });
    }
}
