@extends('layouts.app')

@section('page-title', 'Wallet Balances')

@section('content')
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Crypto Wallets
            </h1>
            <p class="text-gray-600">Current cryptocurrency balances and values</p>
        </div>

        <!-- Wallet Cards Grid -->
        <div class="grid gap-6 md:grid-cols-2 mb-8">
            <!-- Bitcoin Card -->
            <div class="p-6 border-2 border-amber-100 rounded-xl bg-amber-50">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-amber-500 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-bold">BTC</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Bitcoin</h2>
                    </div>
                    <span class="text-sm px-2 py-1 bg-amber-100 text-amber-700 rounded-full">BTC</span>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-2xl font-mono font-bold text-amber-700">
                            {{ $balance['btc']['balance'] }}
                            <span class="text-lg">BTC</span>
                        </p>
                        <p class="text-gray-600 text-sm">
                            ≈ ${{ number_format($balance['btc']['usd_value'], 2) }} USD
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-2">
                        <a href="{{ route('bitcoin.topup') }}"
                           class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium text-center transition-colors">
                            Top Up
                        </a>
                        <a href="{{ route('bitcoin.index') }}"
                           class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium text-center transition-colors">
                            Manage
                        </a>
                    </div>
                </div>
            </div>

            <!-- Monero Card -->
            <div class="p-6 border-2 border-orange-100 rounded-xl bg-orange-50">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-bold">XMR</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Monero</h2>
                    </div>
                    <span class="text-sm px-2 py-1 bg-orange-100 text-orange-700 rounded-full">XMR</span>
                </div>

                @if($xmrWallet)
                    <div class="space-y-4">
                        <div>
                            <p class="text-2xl font-mono font-bold text-orange-700">
                                {{ number_format($balance['xmr']['balance'], 12) }}
                                <span class="text-lg">XMR</span>
                            </p>
                            <p class="text-gray-600 text-sm">
                                ≈ ${{ number_format($balance['xmr']['usd_value'], 2) }} USD
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-2">
                            <a href="{{ route('monero.topup') }}" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium text-center transition-colors">
                                Top Up
                            </a>
                            <a href="{{ route('monero.index') }}" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium text-center transition-colors">
                                Manage
                            </a>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800">
                                Monero wallet is currently unavailable. Please contact support.
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button disabled class="flex-1 bg-gray-300 text-gray-500 px-4 py-2 rounded-lg text-sm font-medium text-center cursor-not-allowed">
                                Unavailable
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Total Balance Summary -->
        <div class="mb-8 p-6 bg-gray-50 rounded-xl">
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Portfolio Value</h3>
                <p class="text-3xl font-bold text-gray-900">
                    ${{ number_format($balance['btc']['usd_value'] + $balance['xmr']['usd_value'], 2) }} USD
                </p>
            </div>
        </div>

        <!-- Wallet Info -->
        <div class="mt-8 pt-6 border-t border-gray-100">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="p-4 bg-amber-50 rounded-lg">
                    <h4 class="text-sm font-semibold text-amber-700 mb-2">Bitcoin Wallet</h4>
                    <p class="text-xs text-gray-600">
                        Your BTC wallet supports direct cryptocurrency deposits.
                    </p>
                </div>
                <div class="p-4 bg-orange-50 rounded-lg">
                    <h4 class="text-sm font-semibold text-orange-700 mb-2">Monero Wallet</h4>
                    <p class="text-xs text-gray-600">
                        XMR transactions benefit from Monero's private ledger technology.
                        Balances are updated after blockchain confirmations.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
