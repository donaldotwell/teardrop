@extends('layouts.app')

@section('page-title', 'Bitcoin Transaction Details')

@section('content')
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Transaction Details</h1>
                <p class="text-gray-600">Bitcoin {{ ucfirst($transaction->type) }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ $transaction->explorer_url }}"
                   target="_blank"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    View on Explorer
                </a>
                <a href="{{ route('bitcoin.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                    Back to Wallet
                </a>
            </div>
        </div>

        <!-- Transaction Summary -->
        <div class="grid gap-6 md:grid-cols-2 mb-8">
            <!-- Amount & Status -->
            <div class="p-6 bg-gradient-to-br from-amber-50 to-white border-2 border-amber-100 rounded-xl">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Transaction Amount</h3>
                <div class="space-y-2">
                    <p class="text-3xl font-mono font-bold text-amber-700">
                        {{ $transaction->type === 'deposit' ? '+' : '-' }}{{ number_format($transaction->amount, 8) }} BTC
                    </p>
                    <p class="text-lg text-gray-600">
                        ≈ ${{ number_format(\App\Repositories\BitcoinRepository::convertToUsd($transaction->amount), 2) }} USD
                    </p>
                    @if($transaction->fee > 0)
                        <p class="text-sm text-gray-500">
                            Network Fee: {{ number_format($transaction->fee, 8) }} BTC
                        </p>
                    @endif
                </div>
            </div>

            <!-- Status & Confirmations -->
            <div class="p-6 bg-gray-50 rounded-xl">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Transaction Status</h3>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 rounded-full mr-3
                            {{ $transaction->status_color === 'green' ? 'bg-green-400' :
                               ($transaction->status_color === 'yellow' ? 'bg-yellow-400' : 'bg-red-400') }}">
                        </span>
                        <span class="text-lg font-semibold
                            {{ $transaction->status_color === 'green' ? 'text-green-700' :
                               ($transaction->status_color === 'yellow' ? 'text-yellow-700' : 'text-red-700') }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Confirmations:</span> {{ $transaction->confirmations }}
                        </p>
                        @if($transaction->confirmations > 0)
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full"
                                     style="width: {{ min(($transaction->confirmations / 6) * 100, 100) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500">
                                {{ $transaction->confirmations }}/6 confirmations
                                @if($transaction->confirmations >= 6)
                                    (Fully Confirmed)
                                @else
                                ({{ 6 - $transaction->confirmations }} more needed)
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction Information</h3>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Transaction ID</label>
                        <div class="flex items-center space-x-2">
                            <label for="txid-field" class="block text-sm text-gray-600 mb-2">
                                Transaction ID (click to select)
                            </label>
                            <input type="text"
                                   value="{{ $transaction->txid }}"
                                   readonly
                                   class="w-full p-2 bg-gray-50 border border-gray-300 rounded font-mono text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 select-all cursor-pointer"
                                   id="txid-field"
                                   onclick="this.select()">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Type</label>
                        <p class="p-2 text-sm">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $transaction->type === 'deposit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Date Created</label>
                        <p class="p-2 text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y H:i:s') }}</p>
                    </div>

                    @if($transaction->confirmed_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Date Confirmed</label>
                            <p class="p-2 text-sm text-gray-900">{{ $transaction->confirmed_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    @endif

                    @if($transaction->block_hash)
                        <div>
                            <label for="block-hash-field" class="block text-sm font-medium text-gray-500 mb-1">
                                Block Hash (click to select)
                            </label>
                            <input type="text"
                                   value="{{ $transaction->block_hash }}"
                                   readonly
                                   class="w-full p-2 bg-gray-50 border border-gray-300 rounded font-mono text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 select-all cursor-pointer"
                                   id="block-hash-field"
                                   onclick="this.select()">
                        </div>
                    @endif

                    @if($transaction->block_height)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Block Height</label>
                            <p class="p-2 text-sm text-gray-900">#{{ number_format($transaction->block_height) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Address Information -->
            @if($transaction->btcAddress)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Address Information</h3>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="address-field" class="block text-sm font-medium text-gray-500 mb-1">
                                Bitcoin Address (click to select)
                            </label>
                            <input type="text"
                                   value="{{ $transaction->btcAddress->address }}"
                                   readonly
                                   class="w-full p-2 bg-gray-50 border border-gray-300 rounded font-mono text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 select-all cursor-pointer"
                                   id="address-field"
                                   onclick="this.select()">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Address Index</label>
                            <p class="p-2 text-sm text-gray-900">#{{ $transaction->btcAddress->address_index }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Address Balance</label>
                            <p class="p-2 text-sm text-gray-900">{{ number_format($transaction->btcAddress->balance, 8) }} BTC</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Total Received</label>
                            <p class="p-2 text-sm text-gray-900">{{ number_format($transaction->btcAddress->total_received, 8) }} BTC</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Raw Transaction Data -->
            @if($transaction->raw_transaction)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Raw Transaction Data</h3>
                    <div class="bg-gray-50 p-4 rounded-lg overflow-x-auto">
                        <pre class="text-xs text-gray-700">{{ json_encode($transaction->raw_transaction, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="mt-8 flex justify-center space-x-4">
            <a href="{{ $transaction->explorer_url }}"
               target="_blank"
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                View on Blockchain Explorer
            </a>
            <a href="{{ route('bitcoin.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium transition-colors">
                Back to Bitcoin Wallet
            </a>
        </div>
    </div>
@endsection
