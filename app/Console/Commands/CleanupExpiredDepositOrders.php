<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredDepositOrders extends Command
{
    protected $signature = 'orders:cleanup-expired-deposits';
    protected $description = 'Cancel pending direct-deposit orders whose deposit window has expired without payment';

    public function handle(): int
    {
        $expired = Order::where('deposit_expires_at', '<', now())
            ->whereNull('escrow_funded_at')
            ->whereNull('cancelled_at')
            ->whereNotNull('deposit_expires_at')
            ->with(['escrowWallet'])
            ->get();

        if ($expired->isEmpty()) {
            Log::info('CleanupExpiredDepositOrders: no expired deposits found');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($expired as $order) {
            try {
                $order->update([
                    'status'              => 'cancelled',
                    'cancelled_at'        => now(),
                    'cancellation_reason' => 'Deposit not received within the 48-hour window.',
                ]);

                if ($order->escrowWallet) {
                    $order->escrowWallet->update(['status' => 'archived']);
                }

                NotificationService::send(
                    $order->user_id,
                    'order',
                    'Order Cancelled — Deposit Expired',
                    "Your order #{$order->uuid} was cancelled because no deposit was received within 48 hours. Please create a new order if you still wish to purchase.",
                    route('orders.index')
                );

                $count++;
                Log::info("CleanupExpiredDepositOrders: cancelled order #{$order->id} (uuid: {$order->uuid})");
            } catch (\Exception $e) {
                Log::error("CleanupExpiredDepositOrders: failed for order #{$order->id}: {$e->getMessage()}");
            }
        }

        Log::info("CleanupExpiredDepositOrders: cancelled {$count} expired deposit order(s)");
        return self::SUCCESS;
    }
}
