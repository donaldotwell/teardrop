@extends('layouts.app')
@section('page-title', 'Complete Security Setup')

@section('breadcrumbs')
    <span class="text-amber-700">Security Setup</span>
@endsection

@section('page-heading')
    Complete Your Security Setup
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">

        {{-- Welcome Message --}}
        <div class="mb-8 p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h2 class="text-lg font-semibold text-yellow-800 mb-2">Welcome to {{ config('app.name') }}!</h2>
            <p class="text-yellow-700 mb-4">
                Your account has been created successfully. To ensure maximum security, please set up your PIN and recovery passphrases.
            </p>
            <div class="text-sm text-yellow-600">
                <p class="font-medium mb-1">These security measures protect:</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li>Your cryptocurrency wallets</li>
                    <li>Sensitive account actions</li>
                    <li>Account recovery processes</li>
                </ul>
            </div>
        </div>

        {{-- Security Setup Form --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form action="{{ route('profile.security.update') }}" method="post" class="space-y-6">
                @csrf

                {{-- PIN Section --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Security PIN</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="pin" class="block text-sm font-medium text-gray-700">6-Digit PIN *</label>
                            <input type="password"
                                   name="pin"
                                   id="pin"
                                   class="block w-full px-3 py-2 border @error('pin') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                   placeholder="000000"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   required>
                            <p class="text-xs text-gray-500">Used for wallet transactions and sensitive actions</p>
                            @error('pin')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="pin_confirmation" class="block text-sm font-medium text-gray-700">Confirm PIN *</label>
                            <input type="password"
                                   name="pin_confirmation"
                                   id="pin_confirmation"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                   placeholder="000000"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   required>
                            <p class="text-xs text-gray-500">Re-enter your PIN</p>
                        </div>
                    </div>
                </div>

                {{-- Recovery Passphrases Section --}}
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recovery Passphrases</h3>

                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label for="passphrase_1" class="block text-sm font-medium text-gray-700">Primary Recovery Passphrase *</label>
                            <input type="text"
                                   name="passphrase_1"
                                   id="passphrase_1"
                                   value="{{ old('passphrase_1') }}"
                                   class="block w-full px-3 py-2 border @error('passphrase_1') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                   placeholder="Enter a memorable phrase (5-64 characters)"
                                   required>
                            <p class="text-sm font-medium text-red-700">
                                CRITICAL: Store this securely offline - required for account recovery
                            </p>
                            @error('passphrase_1')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="passphrase_2" class="block text-sm font-medium text-gray-700">Secondary Recovery Passphrase (Optional)</label>
                            <input type="text"
                                   name="passphrase_2"
                                   id="passphrase_2"
                                   value="{{ old('passphrase_2') }}"
                                   class="block w-full px-3 py-2 border @error('passphrase_2') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                   placeholder="Additional recovery phrase (optional)">
                            <p class="text-xs text-gray-500">
                                Additional security layer - also store securely if used
                            </p>
                            @error('passphrase_2')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Important Notice --}}
                <div class="border-t border-gray-100 pt-6">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="font-medium text-red-800 mb-2">Important Security Notice</h4>
                        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                            <li>Write down your recovery passphrases and store them safely offline</li>
                            <li>Never share your PIN or passphrases with anyone</li>
                            <li>Loss of these credentials may result in permanent account lockout</li>
                            <li>{{ config('app.name') }} cannot recover lost security credentials</li>
                        </ul>
                    </div>
                </div>

                {{-- Submit Actions --}}
                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 py-3 px-6 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">
                        Complete Security Setup
                    </button>
                    <a href="{{ route('home') }}"
                       class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded hover:bg-gray-50">
                        Skip for Now
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
