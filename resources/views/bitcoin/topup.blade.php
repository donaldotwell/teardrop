@extends('layouts.app')

@section('page-title', 'Bitcoin Top-up')

@section('breadcrumbs')
    <a href="{{ route('wallet.index') }}" class="text-gray-600 hover:text-purple-600">Wallets</a>
    <span class="text-gray-400 mx-2">/</span>
    <a href="{{ route('bitcoin.index') }}" class="text-gray-600 hover:text-purple-600">Bitcoin</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-900 font-medium">Top-up</span>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <span class="border-l-4 border-amber-500 pl-3">Deposit Bitcoin</span>
                    </h1>
                    <p class="text-gray-600 mt-1 ml-4">Send Bitcoin to your unique deposit address</p>
                </div>
                <a href="{{ route('bitcoin.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                    Back to Wallet
                </a>
            </div>
        </div>

        <!-- Current BTC Price -->
        <!-- Current BTC Price -->
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-amber-800">Current Bitcoin Price:</span>
                <span class="text-lg font-bold text-amber-900">${{ number_format($btcPrice, 2) }} USD</span>
            </div>
        </div>

        <!-- Main Deposit Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="grid lg:grid-cols-5 divide-y lg:divide-y-0 lg:divide-x divide-gray-200">
                
                <!-- QR Code Section -->
                <div class="lg:col-span-2 p-8">
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="mb-4">
                            @if($qrCodeDataUri)
                                <img src="{{ $qrCodeDataUri }}" alt="Bitcoin Address QR Code" class="w-64 h-64 rounded-lg shadow-md">
                            @else
                                <div class="w-64 h-64 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                    <p class="text-gray-500 text-sm">QR Code unavailable</p>
                                </div>
                            @endif
                        </div>
                        <p class="text-center text-sm text-gray-600 max-w-xs">
                            Scan this code with your Bitcoin wallet to auto-fill the address
                        </p>
                    </div>
                </div>

                <!-- Address Section -->
                <div class="lg:col-span-3 p-8">
                    <div class="space-y-6">
                        <!-- Title -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Deposit Address</h3>
                            <p class="text-sm text-gray-600">Send Bitcoin to this address to fund your wallet</p>
                        </div>

                        <!-- Address Input -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Bitcoin Address</label>
                            <input type="text"
                                   value="{{ $currentAddress->address }}"
                                   readonly
                                   class="w-full p-4 bg-gray-50 border-2 border-gray-200 rounded-lg font-mono text-sm select-all cursor-text hover:border-amber-300 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all"
                                   id="bitcoin-address">
                            <p class="text-xs text-gray-500">
                                Click the address to select it, then copy with Ctrl+C (Cmd+C on Mac)
                            </p>
                        </div>

                        <!-- Important Notice -->
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                            <div class="text-sm text-blue-900">
                                <p class="font-semibold mb-1">Important Notice</p>
                                <p>Only send <strong>Bitcoin (BTC)</strong> to this address. Sending any other cryptocurrency will result in permanent loss of funds.</p>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Minimum Deposit</p>
                                <p class="text-sm font-semibold text-gray-900">0.00001 BTC</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Confirmations Required</p>
                                <p class="text-sm font-semibold text-gray-900">3 blocks</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">How to Deposit</h3>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">Option 1: Using QR Code</h4>
                    <ol class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <span class="bg-amber-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">1</span>
                            Open your Bitcoin wallet app
                        </li>
                        <li class="flex items-start">
                            <span class="bg-amber-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">2</span>
                            Scan the QR code above
                        </li>
                        <li class="flex items-start">
                            <span class="bg-amber-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">3</span>
                            Enter the amount you want to send
                        </li>
                        <li class="flex items-start">
                            <span class="bg-amber-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">4</span>
                            Confirm and send the transaction
                        </li>
                    </ol>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">Option 2: Manual Address</h4>
                    <ol class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <span class="bg-purple-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">1</span>
                            Copy the Bitcoin address above
                        </li>
                        <li class="flex items-start">
                            <span class="bg-purple-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">2</span>
                            Paste it in your wallet's send field
                        </li>
                        <li class="flex items-start">
                            <span class="bg-purple-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">3</span>
                            Enter amount and send
                        </li>
                        <li class="flex items-start">
                            <span class="bg-purple-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">4</span>
                            Wait for blockchain confirmation
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Important Notes -->
        <div class="mt-6 p-6 bg-yellow-50 border border-yellow-200 rounded-xl">
            <h3 class="text-lg font-semibold text-yellow-800 mb-3">Important Notes</h3>

            <div class="space-y-3 text-sm text-yellow-700">
                <div class="flex items-start">
                    <span class="text-yellow-600 mr-2">•</span>
                    <p><strong>Minimum Deposit:</strong> 0.0001 BTC (to cover network fees)</p>
                </div>
                <div class="flex items-start">
                    <span class="text-yellow-600 mr-2">•</span>
                    <p><strong>Confirmations:</strong> Deposits are credited after 1 blockchain confirmation (usually 10-20 minutes). Bitcoin standard is 6 confirmations for full security.</p>
                </div>
                <div class="flex items-start">
                    <span class="text-yellow-600 mr-2">•</span>
                    <p><strong>Network Fees:</strong> Bitcoin network fees are paid by the sender (you)</p>
                </div>
                <div class="flex items-start">
                    <span class="text-yellow-600 mr-2">•</span>
                    <p><strong>Only Bitcoin:</strong> Only send Bitcoin (BTC) to this address. Other cryptocurrencies will be lost.</p>
                </div>
                <div class="flex items-start">
                    <span class="text-yellow-600 mr-2">•</span>
                    <p><strong>Address Rotation:</strong> When your deposit confirms, this address is marked as used and a new address is automatically generated for your next deposit, enhancing privacy.</p>
                </div>
            </div>
        </div>

        <!-- Current Wallet Balance -->
        <div class="mt-6 p-6 bg-amber-50 border border-amber-200 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-amber-800">Current Wallet Balance</h3>
                    <p class="text-2xl font-mono font-bold text-amber-900">
                        {{ number_format($btcWallet->balance, 8) }} BTC
                    </p>
                    <p class="text-sm text-amber-700">
                        ≈ ${{ number_format(\App\Repositories\BitcoinRepository::convertToUsd($btcWallet->balance), 2) }} USD
                    </p>
                </div>
                <div class="text-right">
                    <a href="{{ route('bitcoin.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition-colors">
                        View Transactions
                    </a>
                </div>
            </div>
        </div>

        <!-- Back Navigation -->
        <div class="mt-8 text-center">
            <a href="{{ route('wallet.index') }}"
               class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium">
                ← Back to All Wallets
            </a>
        </div>
    </div>
@endsection
