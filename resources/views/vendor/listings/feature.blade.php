@extends('layouts.vendor')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">Feature This Listing</h1>
            <p class="text-sm text-gray-600 mt-1">Make your listing stand out with featured placement</p>
        </div>

        <!-- Listing Preview -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-start gap-4 bg-gray-50 rounded-lg p-4">
                @if($listing->media->first())
                    <img src="{{ $listing->media->first()->data_uri }}"
                         alt="{{ $listing->title }}"
                         class="w-24 h-24 object-contain bg-white border border-gray-200 rounded-lg p-2">
                @else
                    <div class="w-24 h-24 flex items-center justify-center bg-white border border-gray-200 rounded-lg">
                        <span class="text-gray-400 text-xs">No Image</span>
                    </div>
                @endif
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900">{{ $listing->title }}</h3>
                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $listing->short_description }}</p>
                    <div class="mt-2">
                        <span class="text-lg font-bold text-purple-700">${{ number_format($listing->price, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature Benefits -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Featured Listing Benefits</h2>
            <ul class="space-y-3">
                <li class="flex items-start gap-3">
                    <span class="text-purple-600 font-bold">+</span>
                    <span class="text-sm text-gray-700">Priority placement at the top of search results</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-purple-600 font-bold">+</span>
                    <span class="text-sm text-gray-700">Highlighted with special featured badge</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-purple-600 font-bold">+</span>
                    <span class="text-sm text-gray-700">Increased visibility to potential buyers</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-purple-600 font-bold">+</span>
                    <span class="text-sm text-gray-700">Featured status lasts for 30 days</span>
                </li>
            </ul>
        </div>

        <!-- Pricing -->
        <div class="p-6 border-b border-gray-200 bg-yellow-50">
            <div class="text-center mb-6">
                <div class="text-4xl font-bold text-gray-900 mb-2">${{ number_format($feeUsd, 2) }} USD</div>
                <div class="text-sm text-gray-600">One-time fee for 30-day featured placement</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="text-xs text-gray-500 mb-1">Bitcoin (BTC)</div>
                    <div class="text-lg font-bold text-gray-900">{{ $btcAmount }} BTC</div>
                    @if($btcWallet)
                        <div class="text-xs text-gray-600 mt-2">Your balance: {{ number_format($btcWallet->balance, 8) }} BTC</div>
                    @endif
                </div>
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="text-xs text-gray-500 mb-1">Monero (XMR)</div>
                    <div class="text-lg font-bold text-gray-900">{{ $xmrAmount }} XMR</div>
                    @if($xmrWallet)
                        <div class="text-xs text-gray-600 mt-2">Your balance: {{ number_format($xmrWallet->balance, 8) }} XMR</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <form action="{{ route('vendor.listings.feature', $listing) }}" method="post" class="p-6">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Select Payment Currency</label>
                <div class="space-y-3">
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-500 has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 transition-all">
                        <input type="radio" name="currency" value="btc" required
                               class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500">
                        <span class="ml-3 flex-1">
                            <span class="block text-sm font-medium text-gray-900">Bitcoin (BTC)</span>
                            <span class="block text-xs text-gray-600">Amount: {{ $btcAmount }} BTC</span>
                            @if($btcWallet)
                                <span class="block text-xs text-gray-500 mt-1">Available: {{ number_format($btcWallet->balance, 8) }} BTC</span>
                            @endif
                        </span>
                    </label>

                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-500 has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 transition-all">
                        <input type="radio" name="currency" value="xmr" required
                               class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500">
                        <span class="ml-3 flex-1">
                            <span class="block text-sm font-medium text-gray-900">Monero (XMR)</span>
                            <span class="block text-xs text-gray-600">Amount: {{ $xmrAmount }} XMR</span>
                            @if($xmrWallet)
                                <span class="block text-xs text-gray-500 mt-1">Available: {{ number_format($xmrWallet->balance, 8) }} XMR</span>
                            @endif
                        </span>
                    </label>
                </div>
                @error('currency')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Warning Notice -->
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-yellow-900 mb-2">Important Notice</h4>
                <ul class="text-xs text-yellow-800 space-y-1">
                    <li>Payment will be deducted from your selected wallet</li>
                    <li>Featured status will be active once payment is confirmed</li>
                    <li>This action cannot be undone</li>
                    <li>Refunds are not available after payment is processed</li>
                </ul>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center gap-4">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors">
                    Feature Listing Now
                </button>
                <a href="{{ route('vendor.listings.index') }}"
                   class="flex-1 px-6 py-3 text-center bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
