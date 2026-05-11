@extends('layouts.app')

@section('page-title', 'Awaiting Deposit')

@section('breadcrumbs')
    <a href="{{ route('orders.index') }}" class="text-amber-700 hover:text-amber-900">Orders</a>
    <span class="text-amber-400">/</span>
    <span class="text-amber-700">Deposit Pending</span>
@endsection

@section('page-heading', 'Deposit Awaiting')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    {{-- Status indicator --}}
    @if($order->escrow_funded_at)
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm font-semibold text-green-800">Deposit confirmed — your order is active.</p>
            <p class="text-xs text-green-700 mt-1">Funds received on {{ $order->escrow_funded_at->format('d M Y H:i') }} UTC.</p>
        </div>
    @elseif($order->deposit_expires_at && $order->deposit_expires_at->isPast())
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm font-semibold text-red-800">Deposit window expired.</p>
            <p class="text-xs text-red-700 mt-1">This order has been cancelled. Please create a new order if you still wish to purchase.</p>
        </div>
    @else
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-sm font-semibold text-amber-900">Waiting for your deposit.</p>
            @if($order->deposit_expires_at)
                <p class="text-xs text-amber-700 mt-1">
                    Deposit window closes {{ $order->deposit_expires_at->diffForHumans() }}
                    ({{ $order->deposit_expires_at->format('d M Y H:i') }} UTC).
                </p>
            @endif
        </div>
    @endif

    {{-- Deposit Instructions --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
        <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3">
            {{ $order->listing->title }}
        </h2>

        {{-- Amount --}}
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="p-3 bg-gray-50 rounded-lg">
                <div class="text-gray-500 mb-1 text-xs">Order total (USD)</div>
                <div class="font-semibold text-gray-900">${{ number_format($order->usd_price, 2) }}</div>
            </div>
            <div class="p-3 bg-amber-50 rounded-lg">
                <div class="text-gray-500 mb-1 text-xs">Amount to deposit</div>
                <div class="font-bold text-amber-800 font-mono">
                    {{ number_format($order->crypto_value, $order->currency === 'btc' ? 8 : 12) }}
                    {{ strtoupper($order->currency) }}
                </div>
            </div>
        </div>

        {{-- Deposit Address --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-2">Send exactly the amount above to this address:</p>
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="font-mono text-sm text-gray-900 break-all select-all">{{ $order->escrowWallet->address }}</p>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                Send only {{ strtoupper($order->currency) }} to this address. Sending any other currency will result in permanent loss.
            </p>
        </div>

        {{-- Order reference --}}
        <div class="text-xs text-gray-400 pt-2 border-t border-gray-100">
            Order reference: <span class="font-mono">{{ $order->uuid }}</span>
        </div>
    </div>

    {{-- What happens next --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">What happens next</h3>
        <ol class="space-y-2 text-sm text-gray-600 list-decimal list-inside">
            <li>Send the exact {{ strtoupper($order->currency) }} amount to the address above.</li>
            <li>The network confirms your transaction (this may take a few minutes).</li>
            <li>Your order is automatically activated and the vendor is notified.</li>
            <li>The vendor ships your order and you release the escrow once received.</li>
        </ol>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('orders.show', $order) }}"
           class="flex-1 block text-center py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition-colors">
            View Order
        </a>
        <a href="{{ route('orders.index') }}"
           class="flex-1 block text-center py-2.5 border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-semibold rounded-lg transition-colors">
            My Orders
        </a>
    </div>

</div>
@endsection
