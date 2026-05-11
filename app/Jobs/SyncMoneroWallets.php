<?php

namespace App\Jobs;

use App\Models\EscrowWallet;
use App\Models\UserMessage;
use App\Models\XmrWallet;
use App\Repositories\MoneroRepository;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMoneroWallets implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $uniqueFor = 600;

    public function uniqueId(): string
    {
        return 'monero-wallet-sync';
    }

    public function handle(): void
    {
        Log::debug('SyncMoneroWallets dispatcher started');

        $repository = new MoneroRepository();

        if (!$repository->isRpcAvailable()) {
            Log::warning('Monero RPC unavailable — skipping sync dispatch');
            return;
        }

        // Only sync wallets that have had recent activity or have pending transactions.
        $window = config('monero.sync_active_window_hours', 24);

        $dispatched = 0;
        XmrWallet::where('is_active', true)
            ->where(function ($q) use ($window) {
                $q->where('last_active_at', '>=', now()->subHours($window))
                  ->orWhereHas('transactions', fn ($tq) => $tq->where('status', 'pending'));
            })
            ->chunkById(100, function ($wallets) use (&$dispatched) {
                foreach ($wallets as $wallet) {
                    // SyncSingleXmrWallet targets 'wallet-sync-xmr' — must run with 1 worker.
                    SyncSingleXmrWallet::dispatch($wallet->id);
                    $dispatched++;
                }
            });

        Log::info("SyncMoneroWallets: dispatched {$dispatched} per-wallet sync jobs");

        // Escrow wallets sync inline — small set, needs timely funded-order detection.
        $escrowCount = 0;
        EscrowWallet::where('currency', 'xmr')
            ->where('status', 'active')
            ->chunkById(50, function ($escrowWallets) use ($repository, &$escrowCount) {
                foreach ($escrowWallets as $escrowWallet) {
                    try {
                        $wasUnfunded = !$escrowWallet->order->escrow_funded_at;
                        $repository->syncEscrowWallet($escrowWallet);
                        $escrowCount++;

                        // Send notifications if this sync just confirmed the deposit
                        if ($wasUnfunded && $escrowWallet->order->fresh()->escrow_funded_at) {
                            $order = $escrowWallet->order->load(['user', 'listing.user']);

                            UserMessage::create([
                                'sender_id'   => $order->user_id,
                                'receiver_id' => $order->listing->user_id,
                                'message'     => "New order #{$order->uuid}: {$order->quantity}x \"{$order->listing->title}\" — {$order->crypto_value} XMR deposited to escrow.",
                                'order_id'    => $order->id,
                            ]);

                            NotificationService::send(
                                $order->listing->user_id,
                                'order',
                                'New Order Received',
                                "Order #{$order->uuid} — {$order->crypto_value} XMR confirmed in escrow.",
                                route('orders.show', $order)
                            );

                            NotificationService::send(
                                $order->user_id,
                                'order',
                                'Deposit Confirmed',
                                "Your deposit for order #{$order->uuid} has been confirmed. The vendor will be notified.",
                                route('orders.show', $order)
                            );

                            Log::info("Notifications sent for funded XMR escrow order #{$order->id}");
                        }
                    } catch (\Exception $e) {
                        Log::error("Escrow wallet sync failed for order #{$escrowWallet->order_id}: {$e->getMessage()}");
                    }
                }
            });

        Log::debug("SyncMoneroWallets: synced {$escrowCount} escrow wallets inline");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncMoneroWallets dispatcher failed: ' . $exception->getMessage());
    }
}
