<?php

namespace App\Jobs;

use App\Models\CartItem;
use App\Models\Listing;
use App\Models\User;
use App\Models\UserMessage;
use App\Services\EscrowService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessCartItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // No retries — if escrow funding reached the blockchain and then something
    // else failed, a retry would double-charge the buyer.
    public int $tries   = 1;
    public int $timeout = 120;

    public function __construct(
        public readonly int    $userId,
        public readonly int    $cartItemId,
        public readonly int    $listingId,
        public readonly int    $quantity,
        public readonly string $currency,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $user    = User::findOrFail($this->userId);
        $listing = Listing::with('user')->findOrFail($this->listingId);

        $usdPrice    = ($listing->price * $this->quantity) + $listing->price_shipping;
        $cryptoValue = convert_usd_to_crypto($usdPrice, $this->currency);

        $order = DB::transaction(function () use ($user, $listing, $usdPrice, $cryptoValue) {
            $order = $user->orders()->create([
                'listing_id'   => $this->listingId,
                'quantity'     => $this->quantity,
                'currency'     => $this->currency,
                'crypto_value' => $cryptoValue,
                'usd_price'    => $usdPrice,
                'status'       => 'pending',
            ]);

            $escrowService = new EscrowService();
            $escrow        = $escrowService->createEscrowForOrder($order);
            $txid          = $escrowService->fundEscrow($escrow, $order);

            if (!$txid) {
                throw new \Exception("Escrow funding returned no txid for listing #{$this->listingId}.");
            }

            $order->update(['escrow_wallet_id' => $escrow->id, 'txid' => $txid]);

            return $order;
        });

        // Cart item fulfilled — remove it.
        CartItem::where('id', $this->cartItemId)->delete();

        // Message + notification to vendor.
        UserMessage::create([
            'sender_id'   => $this->userId,
            'receiver_id' => $listing->user_id,
            'message'     => "New cart order #{$order->uuid}: {$this->quantity}x \"{$listing->title}\" — {$cryptoValue} " . strtoupper($this->currency) . " in escrow.",
            'order_id'    => $order->id,
        ]);

        NotificationService::send(
            $listing->user_id,
            'order',
            'New Order Received',
            "Cart order #{$order->uuid} — {$cryptoValue} " . strtoupper($this->currency) . " in escrow.",
            route('orders.show', $order)
        );

        // Notification to buyer.
        NotificationService::send(
            $this->userId,
            'order',
            'Order Confirmed',
            "Your order #{$order->uuid} for \"{$listing->title}\" has been placed and is in escrow.",
            route('orders.show', $order)
        );

        Log::info('ProcessCartItem: order created', [
            'order_id'     => $order->id,
            'user_id'      => $this->userId,
            'listing_id'   => $this->listingId,
            'currency'     => $this->currency,
            'crypto_value' => $cryptoValue,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessCartItem permanently failed', [
            'user_id'      => $this->userId,
            'listing_id'   => $this->listingId,
            'cart_item_id' => $this->cartItemId,
            'error'        => $e->getMessage(),
        ]);

        // Notify buyer so they know something went wrong.
        NotificationService::send(
            $this->userId,
            'order',
            'Order Failed',
            "Your cart order for listing #{$this->listingId} could not be processed. Please check your cart balance and try again.",
            route('cart.index')
        );
    }
}
