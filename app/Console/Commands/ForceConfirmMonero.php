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
            // Default: target pending and confirmed transactions
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
        }

        $confirmedCount = 0;
        $unlockedCount = 0;

        foreach ($transactions as $transaction) {
            $oldStatus = $transaction->status;
            $newStatus = null;

            // Determine new status
            if ($transaction->status === 'pending') {
                $newStatus = 'confirmed';
                $transaction->confirmed_at = now();
                $transaction->confirmations = 1;
                $confirmedCount++;
            } elseif ($transaction->status === 'confirmed') {
                $newStatus = 'unlocked';
                $transaction->unlocked_at = now();
                $transaction->confirmations = config('monero.min_confirmations', 10);
                $unlockedCount++;
            }

            if ($newStatus) {
                $this->line("[{$transaction->id}] {$oldStatus} → {$newStatus} | TXID: " . substr($transaction->txid, 0, 16) . '...');

                if (!$isDryRun) {
                    $transaction->status = $newStatus;
                    $transaction->save();

                    // Process confirmation if now unlocked
                    if ($newStatus === 'unlocked') {
                        try {
                            $transaction->processConfirmation();
                        } catch (\Exception $e) {
                            $this->error("  Failed to process confirmation: {$e->getMessage()}");
                        }
                    }
                }
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->table(
            ['Action', 'Count'],
            [
                ['Pending → Confirmed', $confirmedCount],
                ['Confirmed → Unlocked', $unlockedCount],
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
