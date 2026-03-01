@extends('layouts.admin')

@section('page-title', 'Transfer BTC')
@section('page-heading', 'Transfer BTC')
@section('page-description', 'Send Bitcoin from wallet: ' . $btcWallet->name)

@section('breadcrumbs')
    <a href="{{ route('admin.wallets.btc.index') }}" class="text-yellow-700 hover:text-yellow-800">BTC Wallets</a>
    <span class="text-gray-400">/</span>
    <a href="{{ route('admin.wallets.btc.show', $btcWallet) }}" class="text-yellow-700 hover:text-yellow-800">{{ $btcWallet->name }}</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium">Transfer</span>
@endsection

@section('content')

    <div class="max-w-2xl">

        {{-- Warning --}}
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-red-900 mb-1">Security Notice</h3>
            <p class="text-sm text-red-800">
                This transfer requires PGP verification. You will be asked to decrypt a challenge message
                with your private key before the transfer is executed. Double-check the address and amount.
            </p>
        </div>

        {{-- Wallet Info --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Wallet:</span>
                    <span class="font-medium text-gray-900 ml-1">{{ $btcWallet->name }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Owner:</span>
                    <span class="font-medium text-gray-900 ml-1">{{ $btcWallet->user ? $btcWallet->user->username_pub : 'Escrow' }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Balance:</span>
                    <span class="font-mono font-bold text-gray-900 ml-1">{{ number_format($btcWallet->balance, 8) }} BTC</span>
                </div>
                <div>
                    <span class="text-gray-600">~ USD:</span>
                    <span class="font-medium text-gray-900 ml-1">${{ number_format(convert_crypto_to_usd($btcWallet->balance, 'btc'), 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Transfer Form --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Transfer Details</h3>

            <form action="{{ route('admin.wallets.btc.transfer-initiate', $btcWallet) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                        Destination Address <span class="text-red-600">*</span>
                    </label>
                    <input type="text"
                           name="address"
                           id="address"
                           value="{{ old('address') }}"
                           class="w-full px-3 py-2 border @error('address') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-amber-500 font-mono text-sm"
                           placeholder="Bitcoin address"
                           required>
                    @error('address')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                        Amount (BTC) <span class="text-red-600">*</span>
                    </label>
                    <input type="text"
                           name="amount"
                           id="amount"
                           value="{{ old('amount') }}"
                           class="w-full px-3 py-2 border @error('amount') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-amber-500 font-mono text-sm"
                           placeholder="0.00000000"
                           required>
                    <p class="text-xs text-gray-500 mt-1">Max: {{ number_format($btcWallet->balance, 8) }} BTC</p>
                    @error('amount')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-4">
                    <button type="submit" class="px-6 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm font-medium">
                        Continue to PGP Verification
                    </button>
                    <a href="{{ route('admin.wallets.btc.show', $btcWallet) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection
