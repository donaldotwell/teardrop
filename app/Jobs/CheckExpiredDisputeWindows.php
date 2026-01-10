<?php

namespace App\Jobs;

use App\Services\FinalizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckExpiredDisputeWindows implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(FinalizationService $finalizationService): void
    {
        Log::info('CheckExpiredDisputeWindows job started');

        $count = $finalizationService->checkExpiredDisputeWindows();

        Log::info('CheckExpiredDisputeWindows job completed', [
            'processed_count' => $count,
        ]);
    }
}
