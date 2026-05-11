<?php

namespace App\Jobs;

use App\Models\BtcWallet;
use App\Models\EscrowWallet;
use App\Models\UserMessage;
use App\Repositories\BitcoinRepository;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncBitcoinWallets implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $uniqueFor = 600;

    public function uniqueId(): string
    {
        return 'bitcoin-wallet-sync';
    }

    public function handle(): void
    {
        Log::debug('SyncBitcoinWallets dispatcher started');

        $repository = new BitcoinRepository();

        if (!$repository->isRpcAvailable()) {
            Log::warning('Bitcoin RPC unavailable — skipping sync dispatch');
            return;
        }

        // Only sync wallets that have had recent activity or have pending transactions.
        $window = config('bitcoinrpc.sync_active_window_hours', 24);

        $dispatched = 0;
        BtcWallet::where('is_active', true)
            ->where(function ($q) use ($window) {
                $q->where('last_active_at', '>=', now()->subHours($window))
                  ->orWhereHas('transactions', fn ($tq) => $tq->where('status', 'pending'));
            })
            ->chunkById(100, function ($wallets) use (&$dispatched) {
                foreach ($wallets as $wallet) {
                    SyncSingleBtcWallet::dispatch($wallet->id);
                    $dispatched++;
                }
            });

        Log::info("SyncBitcoinWallets: dispatched {$dispatched} per-wallet sync jobs");

        // Escrow wallets are few; sync inline to keep funded-order detection timely.
        $escrowCount = 0;
        EscrowWallet::where('currency', 'btc')
            ->where('status', 'active')
            ->chunkById(50, function ($escrowWallets) use (&$escrowCount) {
                foreach ($escrowWallets as $escrowWallet) {
                    try {
                        $btcWallet = BtcWallet::where('name', $escrowWallet->wallet_name)->first();
                        if (!$btcWallet) {
                            continue;
                        }

                        BitcoinRepository::syncWalletTransactions($btcWallet);
                        $escrowWallet->updateBalance();

                        if (!$escrowWallet->order->escrow_funded_at && $escrowWallet->balance > 0) {
                            $order = $escrowWallet->order;
                            $order->update(['escrow_funded_at' => now()]);
                            Log::info("Escrow funded for order #{$escrowWallet->order_id}");

                            $order->load(['user', 'listing.user']);

                            UserMessage::create([
                                'sender_id'   => $order->user_id,
                                'receiver_id' => $order->listing->user_id,
                                'message'     => "New order #{$order->uuid}: {$order->quantity}x \"{$order->listing->title}\" — {$order->crypto_value} BTC deposited to escrow.",
                                'order_id'    => $order->id,
                            ]);

                            NotificationService::send(
                                $order->listing->user_id,
                                'order',
                                'New Order Received',
                                "Order #{$order->uuid} — {$order->crypto_value} BTC confirmed in escrow.",
                                route('orders.show', $order)
                            );

                            NotificationService::send(
                                $order->user_id,
                                'order',
                                'Deposit Confirmed',
                                "Your deposit for order #{$order->uuid} has been confirmed. The vendor will be notified.",
                                route('orders.show', $order)
                            );
                        }

                        $escrowCount++;
                    } catch (\Exception $e) {
                        Log::error("Escrow wallet sync failed for order #{$escrowWallet->order_id}: {$e->getMessage()}");
                    }
                }
            });

        Log::debug("SyncBitcoinWallets: synced {$escrowCount} escrow wallets inline");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncBitcoinWallets dispatcher failed: ' . $exception->getMessage());
    }
}
