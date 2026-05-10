@extends('layouts.app')

@section('page-title', 'Cart')

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">&#128722; Cart</h1>
        @if($items->isNotEmpty())
            <span class="text-sm text-gray-500">{{ $items->count() }} item(s) &mdash; expires in {{ $items->min('expires_at')?->diffForHumans() }}</span>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    @if($items->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
            <p class="text-gray-500 mb-3">Your cart is empty.</p>
            <a href="{{ route('home') }}" class="text-sm text-amber-700 hover:underline">Browse listings</a>
        </div>
    @else

    {{-- Items grouped by vendor --}}
    @foreach($vendorGroups as $vendorId => $vendorItems)
    @php $vendor = $vendorItems->first()->listing->user; @endphp
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-700">{{ $vendor->username_pub }}</span>
            <span class="text-xs text-gray-400">{{ $vendorItems->count() }} item(s)</span>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($vendorItems as $item)
            <div class="flex items-center gap-4 px-5 py-4">
                {{-- Thumbnail --}}
                <div class="w-14 h-14 flex-shrink-0 rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                    @if($item->listing->firstMedia)
                        <img src="{{ $item->listing->firstMedia->url }}"
                             alt="{{ $item->listing->title }}"
                             class="w-full h-full object-contain">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs">—</div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <a href="{{ route('listings.show', $item->listing) }}"
                       class="text-sm font-medium text-gray-900 hover:text-amber-700 truncate block">
                        {{ $item->listing->title }}
                    </a>
                    <div class="text-xs text-gray-500 mt-0.5">
                        Qty: {{ $item->quantity }} &bull;
                        ${{ number_format($item->listing->price, 2) }} + ${{ number_format($item->listing->price_shipping, 2) }} shipping
                    </div>
                </div>

                {{-- Subtotal --}}
                <div class="text-sm font-semibold text-amber-700 whitespace-nowrap">
                    ${{ number_format(($item->listing->price * $item->quantity) + $item->listing->price_shipping, 2) }}
                </div>

                {{-- Remove --}}
                <form action="{{ route('cart.destroy', $item) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors ml-2" title="Remove">&#10005;</button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- Summary + Checkout --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6">
        <h2 class="text-sm font-semibold text-gray-800 mb-4">Order Summary</h2>

        <div class="space-y-1.5 text-sm mb-4">
            <div class="flex justify-between text-gray-600">
                <span>Total (USD)</span>
                <span class="font-semibold">${{ number_format($totalUsd, 2) }}</span>
            </div>
            <div class="flex justify-between text-gray-500 text-xs">
                <span>BTC equivalent (+ ~{{ number_format($estBtcFee, 8) }} fee)</span>
                <span>{{ number_format($totalBtc + $estBtcFee, 8) }} BTC</span>
            </div>
            <div class="flex justify-between text-gray-500 text-xs">
                <span>XMR equivalent</span>
                <span>{{ number_format($totalXmr, 8) }} XMR</span>
            </div>
        </div>

        {{-- Balance vs needed --}}
        <div class="grid grid-cols-2 gap-3 mb-5">
            @php
                $btcNeeded    = $totalBtc + $estBtcFee;
                $btcBal       = $user_balance['btc']['balance'] ?? 0;
                $xmrBal       = $user_balance['xmr']['unlocked_balance'] ?? 0;
                $btcSufficient = $btcBal >= $btcNeeded;
                $xmrSufficient = $xmrBal >= $totalXmr;
            @endphp

            {{-- BTC --}}
            <div class="rounded-lg border {{ $btcSufficient ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-3 text-xs">
                <div class="font-semibold text-gray-700 mb-1">Bitcoin</div>
                <div class="text-gray-600">Balance: <span class="{{ $btcSufficient ? 'text-green-700' : 'text-red-600' }} font-medium">{{ number_format($btcBal, 8) }} BTC</span></div>
                <div class="text-gray-600">Need: {{ number_format($btcNeeded, 8) }} BTC</div>
                @if(!$btcSufficient && $btcAddress)
                    <div class="mt-2 pt-2 border-t border-red-200">
                        <div class="text-gray-500 mb-1">Deposit address:</div>
                        <div class="font-mono text-[10px] text-gray-700 break-all">{{ $btcAddress->address }}</div>
                        <div class="text-red-600 mt-1">Short: {{ number_format($btcNeeded - $btcBal, 8) }} BTC</div>
                    </div>
                @endif
            </div>

            {{-- XMR --}}
            <div class="rounded-lg border {{ $xmrSufficient ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-3 text-xs">
                <div class="font-semibold text-gray-700 mb-1">Monero</div>
                <div class="text-gray-600">Balance: <span class="{{ $xmrSufficient ? 'text-green-700' : 'text-red-600' }} font-medium">{{ number_format($xmrBal, 8) }} XMR</span></div>
                <div class="text-gray-600">Need: {{ number_format($totalXmr, 8) }} XMR</div>
                @if(!$xmrSufficient && $xmrAddress)
                    <div class="mt-2 pt-2 border-t border-red-200">
                        <div class="text-gray-500 mb-1">Deposit address:</div>
                        <div class="font-mono text-[10px] text-gray-700 break-all">{{ $xmrAddress->address }}</div>
                        <div class="text-red-600 mt-1">Short: {{ number_format($totalXmr - $xmrBal, 8) }} XMR</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Checkout form --}}
        <form action="{{ route('cart.checkout') }}" method="POST" class="space-y-3">
            @csrf
            <div class="flex gap-4">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="currency" value="btc" checked
                           class="w-4 h-4 text-amber-600 focus:ring-amber-500">
                    <span>Pay with Bitcoin</span>
                    @if(!$btcSufficient)<span class="text-xs text-red-500">(insufficient)</span>@endif
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="currency" value="xmr"
                           class="w-4 h-4 text-orange-600 focus:ring-orange-500">
                    <span>Pay with Monero</span>
                    @if(!$xmrSufficient)<span class="text-xs text-red-500">(insufficient)</span>@endif
                </label>
            </div>
            <button type="submit"
                    class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition-colors">
                Place Orders ({{ $items->count() }} item(s) &mdash; ${{ number_format($totalUsd, 2) }})
            </button>
        </form>

        <p class="text-xs text-gray-400 mt-3">
            Funds go into escrow per order. Delivery info can be sent to each vendor via order messages.
            Cart items expire after 30 days.
        </p>
    </div>

    @endif
</div>
@endsection
