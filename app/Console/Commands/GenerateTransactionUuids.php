<?php

namespace App\Console\Commands;

use App\Models\BtcTransaction;
use App\Models\XmrTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateTransactionUuids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:generate-uuids {--force : Force regenerate UUIDs even if they exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate UUIDs for all transactions that are missing them';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting UUID generation for transactions...');
        $force = $this->option('force');

        // Generate UUIDs for XMR transactions
        $this->info('Processing Monero transactions...');
        $xmrQuery = XmrTransaction::query();
        
        if (!$force) {
            $xmrQuery->whereNull('uuid');
        }
        
        $xmrTransactions = $xmrQuery->get();
        $xmrCount = 0;

        foreach ($xmrTransactions as $transaction) {
            $transaction->uuid = (string) Str::uuid();
            $transaction->save();
            $xmrCount++;
        }

        $this->info("Generated UUIDs for {$xmrCount} Monero transaction(s)");

        // Generate UUIDs for BTC transactions
        $this->info('Processing Bitcoin transactions...');
        $btcQuery = BtcTransaction::query();
        
        if (!$force) {
            $btcQuery->whereNull('uuid');
        }
        
        $btcTransactions = $btcQuery->get();
        $btcCount = 0;

        foreach ($btcTransactions as $transaction) {
            $transaction->uuid = (string) Str::uuid();
            $transaction->save();
            $btcCount++;
        }

        $this->info("Generated UUIDs for {$btcCount} Bitcoin transaction(s)");

        // Summary
        $totalCount = $xmrCount + $btcCount;
        $this->newLine();
        $this->info("✓ Successfully generated UUIDs for {$totalCount} total transaction(s)");
        $this->info("  - Monero: {$xmrCount}");
        $this->info("  - Bitcoin: {$btcCount}");

        return Command::SUCCESS;
    }
}
