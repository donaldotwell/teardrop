<?php

namespace App\Console\Commands;

use App\Models\XmrTransaction;
use Illuminate\Console\Command;

class ForceConfirmMonero extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monero:force-confirm
                            {--status=* : Force specific statuses (pending, confirmed, unlocked)}
                            {--dry-run : Show what would be changed without actually updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force XMR transactions to confirmed/unlocked status (testing only - requires force_confirmations config)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if force confirmations is enabled in config
        if (!config('monero.force_confirmations')) {
            $this->error('Force confirmations is disabled in config. Set MONERO_FORCE_CONFIRMATIONS=true in .env to enable.');
            $this->warn('This feature should only be used in testing/development environments!');
            return 1;
        }

        $this->warn('⚠️  FORCE CONFIRMATION MODE ENABLED ⚠️');
        $this->warn('This should ONLY be used in testing/development!');
        $this->newLine();

        $statusFilter = $this->option('status');
        $isDryRun = $this->option('dry-run');

        // Build query
        $query = XmrTransaction::query();

        // Filter by status if specified
        if (!empty($statusFilter)) {
            $query->whereIn('status', $statusFilter);
        } else {
            // Default: target non-unlocked transactions (pending, confirmed)
            $query->whereIn('status', ['pending', 'confirmed']);
        }

        $transactions = $query->get();

        if ($transactions->isEmpty()) {
            $this->info('No transactions found matching the criteria.');
            return 0;
        }

        $this->info("Found {$transactions->count()} transactions to process.");
        $this->newLine();

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->newLine();
            
            foreach ($transactions as $transaction) {
                $this->line("[{$transaction->id}] {$transaction->status} → unlocked | TXID: " . substr($transaction->txid, 0, 16) . '...');
            }
            
            $unlockedCount = $transactions->count();
        } else {
            // Bulk update all transactions to unlocked status
            $now = now();
            $minConfirmations = config('monero.min_confirmations', 10);
            
            $transactionIds = $transactions->pluck('id')->toArray();
            
            XmrTransaction::whereIn('id', $transactionIds)->update([
                'status' => 'unlocked',
                'confirmations' => $minConfirmations,
                'unlocked_at' => $now,
                'updated_at' => $now,
            ]);
            
            // Update confirmed_at only for transactions that don't have it set
            XmrTransaction::whereIn('id', $transactionIds)
                ->whereNull('confirmed_at')
                ->update(['confirmed_at' => $now]);
            
            $this->info("✓ Bulk updated {$transactions->count()} transactions to unlocked status");
            $this->newLine();
            
            // Process confirmations for deposits (updates balances)
            $deposits = $transactions->where('type', 'deposit');
            $balanceUpdates = 0;
            $balanceErrors = 0;
            
            if ($deposits->isNotEmpty()) {
                $this->info("Processing balance updates for {$deposits->count()} deposits...");
                $progressBar = $this->output->createProgressBar($deposits->count());
                $progressBar->start();
                
                foreach ($deposits as $transaction) {
                    try {
                        $transaction->refresh(); // Reload with updated values
                        $transaction->processConfirmation();
                        $balanceUpdates++;
                    } catch (\Exception $e) {
                        $balanceErrors++;
                        \Log::error("Failed to process confirmation for transaction {$transaction->id}: {$e->getMessage()}");
                    }
                    $progressBar->advance();
                }
                
                $progressBar->finish();
                $this->newLine();
                
                if ($balanceErrors > 0) {
                    $this->warn("✓ {$balanceUpdates} balances updated, {$balanceErrors} errors (see logs)");
                } else {
                    $this->info("✓ {$balanceUpdates} balances updated successfully");
                }
            }
            
            $unlockedCount = $transactions->count();
        }

        $this->newLine();
        $this->info('Summary:');
        $this->table(
            ['Action', 'Count'],
            [
                ['Forced to Unlocked', $unlockedCount],
                ['Total Processed', $transactions->count()],
            ]
        );

        if ($isDryRun) {
            $this->newLine();
            $this->info('Dry run complete. Run without --dry-run to apply changes.');
        }

        return 0;
    }
}
