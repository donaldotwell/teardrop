@extends('layouts.app')

@section('page-title', 'Become a Vendor')

@section('breadcrumbs')
    <div class="flex items-center space-x-2 text-sm">
        <a href="{{ route('home') }}" class="text-amber-700 hover:text-amber-600 font-medium transition-colors">
            Home
        </a>
        <span class="text-gray-300">→</span>
        <span class="text-gray-500 font-medium">Become a Vendor</span>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Vendor Account Setup
            </h1>
            <p class="text-gray-600">Upgrade your account to start selling on the marketplace</p>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
            <div class="flex items-start">
                <div>
                    <h3 class="text-lg font-semibold text-red-700">Important Notice</h3>
                    <p class="text-sm text-red-600 mt-1">
                        The vendor registration fee of $1000 USD is non-refundable.
                        Ensure you understand the marketplace rules before proceeding.
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('vendor.convert') }}" class="space-y-6">
            @csrf

            <!-- Payment Method Selection -->
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <!-- BTC Option -->
                    <div class="group">
                        <input type="radio" name="currency" value="btc" id="btc-option" class="sr-only peer" required>
                        <label for="btc-option" class="block border-2 border-gray-200 rounded-lg p-4 cursor-pointer transition-all
            hover:border-amber-400
            peer-checked:border-amber-500
            peer-checked:ring-2
            peer-checked:ring-amber-500/20
            peer-checked:bg-amber-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-lg font-semibold text-gray-900 mb-1">
                                        Pay with Bitcoin
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Required: {{ convert_usd_to_crypto(1000, 'btc') }} BTC
                                    </div>
                                </div>
                                <span class="text-2xl">₿</span>
                            </div>
                            <div class="mt-4 text-sm">
                                <span class="font-medium">Your Balance:</span>
                                {{ $balance['btc']['balance'] }} BTC
                            </div>
                        </label>
                    </div>

                    <!-- XMR Option -->
                    <div class="group">
                        <input type="radio" name="currency" value="xmr" id="xmr-option" class="sr-only peer" required>
                        <label for="xmr-option" class="block border-2 border-gray-200 rounded-lg p-4 cursor-pointer transition-all
            hover:border-purple-400
            peer-checked:border-purple-500
            peer-checked:ring-2
            peer-checked:ring-purple-500/20
            peer-checked:bg-purple-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-lg font-semibold text-gray-900 mb-1">
                                        Pay with Monero
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Required: {{ convert_usd_to_crypto(1000, 'xmr') }} XMR
                                    </div>
                                </div>
                                <span class="text-2xl">ɱ</span>
                            </div>
                            <div class="mt-4 text-sm">
                                <span class="font-medium">Your Balance:</span>
                                {{ $balance['xmr']['balance'] }} XMR
                            </div>
                        </label>
                    </div>
                </div>

                @error('currency')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Terms Agreement -->
            <div class="mt-6">
                <label class="flex items-start">
                    <input type="checkbox" name="terms" class="mt-1 form-checkbox h-4 w-4 text-amber-600 transition-colors" required>
                    <span class="ml-2 text-sm text-gray-600">
                    I agree to the marketplace
                    <a href="#" class="text-amber-700 hover:text-amber-600 underline">Vendor Agreement</a>
                    and understand the $1000 USD fee is non-refundable
                </span>
                </label>
                @error('terms')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full py-3 px-6 bg-gradient-to-r from-amber-600 to-amber-500
                       text-white font-semibold rounded-lg shadow-md hover:shadow-lg
                       transition-all duration-200 transform hover:scale-[1.02]">
                Confirm Vendor Upgrade
            </button>
        </form>
    </div>
@endsection
