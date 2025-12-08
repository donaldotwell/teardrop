@extends('layouts.app')

@section('page-title', 'Monero Top-up')

@section('breadcrumbs')
    <nav class="text-sm mb-6">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="{{ route('wallet.index') }}" class="text-amber-600 hover:text-amber-700">Wallets</a>
                <span class="mx-3 text-gray-400">›</span>
            </li>
            <li class="flex items-center">
                <a href="{{ route('monero.index') }}" class="text-amber-600 hover:text-amber-700">Monero</a>
                <span class="mx-3 text-gray-400">›</span>
            </li>
            <li class="text-gray-500">Top-up</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Deposit Monero</h1>
            <p class="text-gray-600">Send XMR to the address below to add funds to your wallet</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 rounded-r-lg">
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-r-lg">
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Current XMR Price -->
        <div class="mb-6 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium opacity-90">Current Monero Price</span>
                <span class="text-2xl font-bold">${{ number_format($xmrPrice, 2) }}</span>
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
                                <img src="{{ $qrCodeDataUri }}" alt="Monero Address QR Code" class="w-64 h-64 rounded-lg shadow-md">
                            @else
                                <div class="w-64 h-64 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                    <p class="text-gray-500 text-sm">QR Code unavailable</p>
                                </div>
                            @endif
                        </div>
                        <p class="text-center text-sm text-gray-600 max-w-xs">
                            Scan this code with your Monero wallet to auto-fill the address
                        </p>
                    </div>
                </div>

                <!-- Address Section -->
                <div class="lg:col-span-3 p-8">
                    <div class="space-y-6">
                        <!-- Title -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Deposit Address</h3>
                            <p class="text-sm text-gray-600">Send Monero to this address to fund your wallet</p>
                        </div>

                        <!-- Address Input -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Monero Address</label>
                            <input type="text"
                                   value="{{ $currentAddress->address }}"
                                   readonly
                                   onclick="this.select()"
                                   class="w-full p-4 bg-gray-50 border-2 border-gray-200 rounded-lg font-mono text-sm cursor-pointer hover:border-orange-300 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all"
                                   id="monero-address">
                            <p class="text-xs text-gray-500">
                                Click the address to select it, then copy with Ctrl+C (Cmd+C on Mac)
                            </p>
                        </div>

                        <!-- Important Notice -->
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                            <div class="text-sm text-blue-900">
                                <p class="font-semibold mb-1">Important Notice</p>
                                <p>Only send <strong>Monero (XMR)</strong> to this address. Sending any other cryptocurrency will result in permanent loss of funds.</p>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-600 mb-1">Minimum Deposit</p>
                                <p class="text-lg font-bold text-gray-900">0.001 XMR</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-600 mb-1">Confirmations Required</p>
                                <p class="text-lg font-bold text-gray-900">10 blocks</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Instructions & Information -->
        <div class="mt-6 grid md:grid-cols-2 gap-6">
            
            <!-- How to Deposit -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    How to Deposit
                </h3>
                <ol class="space-y-3">
                    <li class="flex items-start text-sm text-gray-700">
                        <span class="flex-shrink-0 w-6 h-6 bg-orange-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">1</span>
                        <span>Copy the Monero address or scan the QR code</span>
                    </li>
                    <li class="flex items-start text-sm text-gray-700">
                        <span class="flex-shrink-0 w-6 h-6 bg-orange-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">2</span>
                        <span>Open your Monero wallet and initiate a transfer</span>
                    </li>
                    <li class="flex items-start text-sm text-gray-700">
                        <span class="flex-shrink-0 w-6 h-6 bg-orange-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">3</span>
                        <span>Send at least 0.001 XMR to the address above</span>
                    </li>
                    <li class="flex items-start text-sm text-gray-700">
                        <span class="flex-shrink-0 w-6 h-6 bg-orange-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">4</span>
                        <span>Wait for 10 confirmations (≈20-30 minutes)</span>
                    </li>
                </ol>
            </div>

            <!-- Important Information -->
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    Important Information
                </h3>
                <ul class="space-y-3 text-sm text-gray-700">
                    <li><strong>Confirmation Time:</strong> 10 blocks (typically 20-30 minutes) until funds are unlocked</li>
                    <li><strong>Network Fees:</strong> Paid by sender (you) to Monero miners</li>
                    <li><strong>Privacy:</strong> Monero transactions are private by default - your balance and transactions are hidden</li>
                    <li><strong>Warning:</strong> Deposits from exchanges may take longer due to exchange withdrawal processing times</li>
                </ul>
            </div>

        </div>

        <!-- Current Balance Card -->
        <div class="mt-6 bg-gradient-to-r from-orange-600 to-orange-700 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90 mb-1">Current Wallet Balance</p>
                    <p class="text-3xl font-bold font-mono">
                        {{ number_format($xmrWallet->balance, 12) }} XMR
                    </p>
                    <p class="text-sm opacity-75 mt-1">
                        ≈ ${{ number_format(\App\Repositories\MoneroRepository::convertToUsd($xmrWallet->balance), 2) }} USD
                    </p>
                </div>
                <a href="{{ route('monero.index') }}"
                   class="px-6 py-3 bg-white text-orange-600 font-semibold rounded-lg hover:bg-orange-50 transition-colors shadow-md">
                    View History
                </a>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-6 text-center">
            <a href="{{ route('wallet.index') }}"
               class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium transition-colors">
                ← Back to Wallets
            </a>
        </div>
    </div>
@endsection
