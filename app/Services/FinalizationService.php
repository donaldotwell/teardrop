<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Listing;
use App\Models\User;
use App\Models\FinalizationWindow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FinalizationService
{
    /**
     * Check if an order can use early finalization.
     *
     * @param \App\Models\Listing $listing
     * @param \App\Models\User $vendor
     * @param \App\Models\User $buyer
     * @return array ['eligible' => bool, 'reason' => string]
     */
    public function canOrderUseEarlyFinalization(Listing $listing, User $vendor, User $buyer): array
    {
        // Check if listing uses direct payment method
        if ($listing->payment_method !== 'direct') {
            return [
                'eligible' => false,
                'reason' => 'Listing does not use direct payment method'
            ];
        }

        // Get product category
        $category = $listing->product->productCategory;

        // Check if category allows early finalization
        if (!$category->allows_early_finalization) {
            return [
                'eligible' => false,
                'reason' => 'Product category does not allow early finalization'
            ];
        }

        // Check if category has a finalization window
        if (!$category->finalization_window_id || !$category->finalizationWindow) {
            return [
                'eligible' => false,
                'reason' => 'Product category does not have a finalization window configured'
            ];
        }

        // Check if finalization window is active
        if (!$category->finalizationWindow->is_active) {
            return [
                'eligible' => false,
                'reason' => 'Finalization window is not active'
            ];
        }

        // Check vendor level
        if ($vendor->vendor_level < $category->min_vendor_level_for_early) {
            return [
                'eligible' => false,
                'reason' => "Vendor level {$vendor->vendor_level} is below minimum required level {$category->min_vendor_level_for_early}"
            ];
        }

        // Check if vendor has early finalization enabled
        if (!$vendor->early_finalization_enabled) {
            return [
                'eligible' => false,
                'reason' => 'Early finalization is disabled for this vendor'
            ];
        }

        // Check vendor status
        if ($vendor->status !== 'active') {
            return [
                'eligible' => false,
                'reason' => 'Vendor account is not active'
            ];
        }

        // All checks passed
        return [
            'eligible' => true,
            'reason' => 'Order qualifies for early finalization'
        ];
    }

    /**
     * Calculate dispute window expiry time.
     *
     * @param \App\Models\FinalizationWindow $window
     * @param \Carbon\Carbon $finalizedAt
     * @return \Carbon\Carbon
     */
    public function calculateDisputeWindowExpiry(FinalizationWindow $window, Carbon $finalizedAt): Carbon
    {
        return $finalizedAt->copy()->addMinutes($window->duration_minutes);
    }

    /**
     * Check and process expired dispute windows.
     *
     * @return int Number of processed orders
     */
    public function checkExpiredDisputeWindows(): int
    {
        $processedCount = 0;

        // Find orders with expired dispute windows
        $orders = Order::where('is_early_finalized', true)
            ->where('dispute_window_expires_at', '<=', now())
            ->whereDoesntHave('dispute')
            ->whereNotIn('status', ['cancelled'])
            ->get();

        foreach ($orders as $order) {
            try {
                Log::info("Dispute window expired for order", [
                    'order_id' => $order->id,
                    'order_uuid' => $order->uuid,
                    'expired_at' => $order->dispute_window_expires_at,
                ]);

                // Update vendor stats
                $vendor = $order->listing->user;
                $vendor->increment('successful_early_finalized_orders');
                $vendor->updateEarlyFinalizationStats();

                // Optionally notify vendor
                \App\Models\UserMessage::create([
                    'sender_id' => 1, // System user
                    'receiver_id' => $vendor->id,
                    'message' => "Dispute window has expired for order #{$order->id}. Payment is now final and irreversible.",
                    'order_id' => $order->id,
                ]);

                $processedCount++;

            } catch (\Exception $e) {
                Log::error("Failed to process expired dispute window", [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($processedCount > 0) {
            Log::info("Processed expired dispute windows", [
                'count' => $processedCount,
            ]);
        }

        return $processedCount;
    }

    /**
     * Get time remaining in dispute window.
     *
     * @param \App\Models\Order $order
     * @return string|null
     */
    public function getDisputeWindowTimeRemaining(Order $order): ?string
    {
        if (!$order->is_early_finalized || !$order->dispute_window_expires_at) {
            return null;
        }

        if ($order->isDisputeWindowExpired()) {
            return 'Expired';
        }

        $now = now();
        $expiry = $order->dispute_window_expires_at;
        $diff = $now->diff($expiry);

        if ($diff->days > 0) {
            return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' remaining';
        }

        if ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' remaining';
        }

        if ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' remaining';
        }

        return 'Less than 1 minute remaining';
    }

    /**
     * Check if dispute window is expiring soon (within threshold).
     *
     * @param \App\Models\Order $order
     * @param int $thresholdMinutes
     * @return bool
     */
    public function isDisputeWindowExpiringSoon(Order $order, int $thresholdMinutes = 60): bool
    {
        if (!$order->is_early_finalized || !$order->dispute_window_expires_at) {
            return false;
        }

        if ($order->isDisputeWindowExpired()) {
            return false;
        }

        $minutesRemaining = now()->diffInMinutes($order->dispute_window_expires_at);
        return $minutesRemaining <= $thresholdMinutes;
    }
}
