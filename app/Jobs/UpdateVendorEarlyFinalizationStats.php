<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateVendorEarlyFinalizationStats implements ShouldQueue
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
    public function handle(): void
    {
        Log::info('UpdateVendorEarlyFinalizationStats job started');

        $vendors = User::where('vendor_level', '>=', config('fees.early_finalization.min_vendor_level', 8))
            ->get();

        $processedCount = 0;
        $disabledCount = 0;

        foreach ($vendors as $vendor) {
            try {
                $vendor->updateEarlyFinalizationStats();

                // Check if dispute rate exceeds threshold and auto-disable is enabled
                if (config('finalization.auto_disable_on_high_dispute_rate', false)) {
                    $disputeRate = $vendor->getEarlyFinalizationDisputeRate();
                    $maxRate = config('fees.early_finalization.max_dispute_rate_percentage', 20.0);

                    if ($disputeRate > $maxRate && $vendor->early_finalization_enabled) {
                        $vendor->update(['early_finalization_enabled' => false]);
                        $disabledCount++;

                        Log::warning('Vendor early finalization disabled due to high dispute rate', [
                            'vendor_id' => $vendor->id,
                            'dispute_rate' => $disputeRate,
                        ]);

                        // Notify admin (create a support ticket or notification)
                        \App\Models\UserMessage::create([
                            'sender_id' => 1, // System
                            'receiver_id' => $vendor->id,
                            'message' => "Early finalization has been disabled for your account due to a dispute rate of {$disputeRate}% which exceeds the maximum allowed rate of {$maxRate}%. Please contact support for more information.",
                        ]);
                    }
                }

                $processedCount++;

            } catch (\Exception $e) {
                Log::error('Failed to update vendor early finalization stats', [
                    'vendor_id' => $vendor->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('UpdateVendorEarlyFinalizationStats job completed', [
            'processed_count' => $processedCount,
            'disabled_count' => $disabledCount,
        ]);
    }
}
