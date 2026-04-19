<?php

namespace App\Console\Commands;

use App\Models\FullzPurchase;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateFullzPurchaseUuids extends Command
{
    protected $signature = 'autoshop:generate-purchase-uuids {--force : Regenerate UUIDs even for records that already have one}';

    protected $description = 'Backfill UUIDs on fullz_purchases rows that are missing them';

    public function handle(): int
    {
        $force = $this->option('force');

        $query = FullzPurchase::query();

        if (!$force) {
            $query->whereNull('uuid');
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('All purchases already have UUIDs — nothing to do.');
            return Command::SUCCESS;
        }

        $this->info("Generating UUIDs for {$total} purchase(s)...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $count = 0;
        $query->chunkById(200, function ($purchases) use ($bar, &$count) {
            foreach ($purchases as $purchase) {
                // Bypass model events to avoid re-triggering boot logic
                FullzPurchase::withoutEvents(function () use ($purchase) {
                    $purchase->uuid = (string) Str::uuid();
                    $purchase->saveQuietly();
                });
                $bar->advance();
                $count++;
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Done. Generated UUIDs for {$count} purchase(s).");

        return Command::SUCCESS;
    }
}
