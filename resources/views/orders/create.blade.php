@extends('layouts.app')

@section('page-title', 'Confirm Order')

@section('breadcrumbs')
    <div class="flex items-center space-x-2 text-sm">
        <a href="{{ route('listings.show', $listing) }}" class="text-amber-700 hover:text-amber-600 font-medium transition-colors">
            {{ $listing->title }}
        </a>
        <span class="text-gray-300">→</span>
        <span class="text-gray-500 font-medium">Confirm Order</span>
    </div>
@endsection

@section('page-heading', 'Confirm Your Order')

@section('content')
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-3xl mx-auto">
        <!-- Order Header -->
        <div class="mb-8 pb-6 border-b border-gray-100">
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight leading-tight">
                <span class="border-l-4 border-amber-500 pl-3">{{ $listing->title }}</span>
            </h2>
            <p class="mt-3 text-gray-500 text-base">
                Review your order details and confirm your purchase.
            </p>

            @if($listing->payment_method === 'direct' && $listing->canUseEarlyFinalization())
                <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <span class="flex-shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-green-100 text-green-800">
                            INSTANT PAYMENT
                        </span>
                        <div class="flex-1">
                            <p class="text-sm text-green-900 font-medium">
                                This vendor accepts early finalization - you'll receive your order immediately!
                            </p>
                            @if($window = $listing->getFinalizationWindow())
                                <p class="text-sm text-green-700 mt-1">
                                    Dispute window: {{ $window->getHumanReadableDuration() }} from purchase
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Order Summary -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="p-4 border border-gray-100 rounded-xl">
                    <div class="text-sm text-gray-500 mb-1">Price</div>
                    <div class="flex items-baseline space-x-1">
                        <span class="text-xl font-semibold text-amber-700">{{ $listing->price }}</span>
                        <span class="text-sm text-gray-500">USD</span>
                    </div>
                </div>

                <div class="p-4 border border-gray-100 rounded-xl">
                    <div class="text-sm text-gray-500 mb-1">Quantity</div>
                    <div class="text-xl font-semibold text-amber-700">{{ $quantity }}</div>
                </div>

                <div class="p-4 border border-gray-100 rounded-xl">
                    <div class="text-sm text-gray-500 mb-1">Total</div>
                    <div class="space-y-1">
                        <div class="text-xl font-semibold text-amber-700">
                            {{ number_format($usd_price, 8) }} USD
                        </div>
                        <div class="text-sm text-gray-500">
                            ≈ {{ $crypto_value }} {{ strtoupper($currency) }}
                        </div>
                    </div>
                </div>
            </div>

            @if($estimated_fee > 0)
            <div class="mt-6 p-4 bg-amber-50 border border-amber-100 rounded-lg">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Transaction Fee Breakdown</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Order Amount:</span>
                        <span class="font-medium text-gray-900">{{ $crypto_value }} {{ strtoupper($currency) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Network Fee (estimated):</span>
                        <span class="font-medium text-amber-700">{{ $estimated_fee }} {{ strtoupper($currency) }}</span>
                    </div>
                    <div class="pt-2 border-t border-amber-200 flex justify-between items-center">
                        <span class="font-semibold text-gray-900">Total Required:</span>
                        <span class="font-bold text-amber-700">{{ $total_needed }} {{ strtoupper($currency) }}</span>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500">
                    Network fee is calculated based on transaction size and current network conditions to ensure fast confirmation.
                </p>
            </div>
            @endif

            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <span class="font-medium">Your {{ strtoupper($currency) }} Balance:</span>
                    <span class="font-semibold text-amber-700">
                        {{ auth()->user()->getBalance()[$currency]['balance'] }} {{ strtoupper($currency) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Order Confirmation Form -->
        <form action="{{ route('orders.store', $listing) }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="currency" value="{{ $currency }}">
            <input type="hidden" name="quantity" value="{{ $quantity }}">

            <div>
                <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-2">
                    Delivery Address
                </label>
                <textarea id="delivery_address" name="delivery_address" rows="4"
                          class="block w-full px-4 py-3 border border-gray-200 rounded-lg shadow-sm
                           focus:ring-2 focus:ring-amber-500 focus:border-amber-500
                           transition-all duration-150 placeholder-gray-400 text-base"
                          placeholder="Enter your full delivery address...&#x0a;Name&#x0a;Street Address&#x0a;City, State/Province&#x0a;Postal Code, Country">{{ old('delivery_address') }}</textarea>
                <p class="mt-2 text-xs text-gray-500">
                    <svg class="inline-block w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Your address will be encrypted with the vendor's PGP public key. Only the vendor can decrypt it.
                </p>
                @error('delivery_address')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="note" class="block text-sm font-medium text-gray-700 mb-2">
                    Note to Vendor
                    <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <textarea id="note" name="note" rows="4"
                          class="block w-full px-4 py-3 border border-gray-200 rounded-lg shadow-sm
                           focus:ring-2 focus:ring-amber-500 focus:border-amber-500
                           transition-all duration-150 placeholder-gray-400 text-base"
                          placeholder="Add any special instructions or messages..."></textarea>
                @error('note')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full py-3 px-6 bg-gradient-to-r from-amber-600 to-amber-500
                           text-white font-semibold rounded-lg shadow-md hover:shadow-lg
                           transition-all duration-200 transform hover:scale-[1.02]">
                Confirm Order
            </button>
        </form>
    </div>
@endsection
