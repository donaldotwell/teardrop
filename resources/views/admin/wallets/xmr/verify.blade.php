@extends('layouts.admin')

@section('page-title', 'Verify XMR Transfer')
@section('page-heading', 'PGP Verification Required')
@section('page-description', 'Decrypt the challenge to authorize this transfer')

@section('breadcrumbs')
    <a href="{{ route('admin.wallets.xmr.index') }}" class="text-yellow-700 hover:text-yellow-800">XMR Wallets</a>
    <span class="text-gray-400">/</span>
    <a href="{{ route('admin.wallets.xmr.show', $xmrWallet) }}" class="text-yellow-700 hover:text-yellow-800">{{ $xmrWallet->name }}</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium">Verify Transfer</span>
@endsection

@section('content')

    <div class="max-w-2xl space-y-6">

        {{-- Transfer Summary --}}
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <h3 class="font-semibold text-amber-900 mb-2">Transfer Summary</h3>
            <div class="space-y-1 text-sm text-amber-800">
                <div><strong>From:</strong> {{ $xmrWallet->name }}</div>
                <div><strong>To:</strong> <span class="font-mono text-xs break-all">{{ $address }}</span></div>
                <div><strong>Amount:</strong> <span class="font-mono font-bold">{{ $amount }} XMR</span></div>
            </div>
        </div>

        {{-- Encrypted Challenge --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-3">Encrypted Challenge Message</h3>
            <p class="text-sm text-gray-600 mb-3">
                Select and copy the entire message below, then decrypt it with your PGP private key:
            </p>

            <div class="bg-amber-50 border-2 border-amber-300 rounded-lg p-4">
                <pre class="bg-gray-900 text-amber-300 p-4 rounded-lg overflow-x-auto text-xs leading-relaxed border border-amber-600 select-all">{{ $encryptedMessage }}</pre>
            </div>

            <div class="mt-4 bg-gray-50 border border-gray-200 rounded p-3">
                <h4 class="text-sm font-semibold text-gray-800 mb-2">How to Decrypt:</h4>
                <ol class="text-xs text-gray-600 space-y-1 list-decimal list-inside">
                    <li>Copy the encrypted message above</li>
                    <li>Save to a file: <code class="bg-gray-100 px-1 rounded">challenge.asc</code></li>
                    <li>Decrypt: <code class="bg-gray-100 px-1 rounded">gpg --decrypt challenge.asc</code></li>
                    <li>Find the "Verification Code:" line</li>
                    <li>Enter the code below</li>
                </ol>
            </div>
        </div>

        {{-- Verification Form --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Submit Verification Code</h3>

            <form action="{{ route('admin.wallets.xmr.transfer-execute', $xmrWallet) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-1">
                        Verification Code <span class="text-red-600">*</span>
                    </label>
                    <input type="text"
                           name="verification_code"
                           id="verification_code"
                           class="w-full px-4 py-3 border @error('verification_code') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-amber-500 font-mono text-lg uppercase tracking-wider"
                           placeholder="Enter code from decrypted message"
                           maxlength="20"
                           value="{{ old('verification_code') }}"
                           autocomplete="off"
                           autofocus
                           required>
                    @error('verification_code')
                        <p class="text-sm text-red-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if($attemptsRemaining < 5)
                    <div class="bg-red-50 border border-red-200 rounded p-3">
                        <p class="text-sm text-red-800">
                            <strong>Warning:</strong> {{ $attemptsRemaining }} attempt(s) remaining.
                        </p>
                    </div>
                @endif

                <div class="flex items-center gap-3 pt-4">
                    <button type="submit" class="px-6 py-3 bg-red-600 text-white font-medium rounded hover:bg-red-700 text-sm">
                        Authorize Transfer
                    </button>
                    <a href="{{ route('admin.wallets.xmr.show', $xmrWallet) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection
