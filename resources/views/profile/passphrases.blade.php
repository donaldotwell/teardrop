{{-- profile/passphrases.blade.php --}}
@extends('layouts.app')
@section('page-title', 'Recovery Passphrases')

@section('breadcrumbs')
    <a href="{{ route('profile.show') }}" class="text-yellow-700 hover:text-yellow-800">Profile</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Recovery Passphrases</span>
@endsection

@section('page-heading')
    Recovery Passphrases
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">

        {{-- Critical Warning --}}
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <h3 class="text-sm font-medium text-red-800 mb-1">🚨 CRITICAL: Account Recovery Information</h3>
            <p class="text-sm text-red-700 mb-2">
                Recovery passphrases are your ONLY way to recover your account if you lose access.
                {{ config('app.name') }} cannot recover lost passphrases.
            </p>
            <p class="text-sm font-semibold text-red-800">
                Store these securely offline before making any changes!
            </p>
        </div>

        {{-- Passphrases Change Form --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form action="{{ route('profile.passphrases.update') }}" method="post" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Current Primary Passphrase --}}
                <div class="space-y-1">
                    <label for="current_passphrase_1" class="block text-sm font-medium text-gray-700">Current Primary Passphrase *</label>
                    <input type="text"
                           name="current_passphrase_1"
                           id="current_passphrase_1"
                           class="block w-full px-3 py-2 border @error('current_passphrase_1') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           autocomplete="off"
                           required>
                    <p class="text-xs text-gray-500">Enter your current primary recovery passphrase to confirm changes</p>
                    @error('current_passphrase_1')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Primary Passphrase --}}
                <div class="space-y-1">
                    <label for="passphrase_1" class="block text-sm font-medium text-gray-700">New Primary Recovery Passphrase *</label>
                    <input type="text"
                           name="passphrase_1"
                           id="passphrase_1"
                           value="{{ old('passphrase_1') }}"
                           class="block w-full px-3 py-2 border @error('passphrase_1') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           placeholder="Enter a memorable but secure phrase (5-64 characters)"
                           autocomplete="off"
                           required>
                    <p class="text-sm font-medium text-red-700">
                        CRITICAL: This is required for account recovery - store it safely offline!
                    </p>
                    @error('passphrase_1')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Secondary Passphrase --}}
                <div class="space-y-1">
                    <label for="passphrase_2" class="block text-sm font-medium text-gray-700">Secondary Recovery Passphrase (Optional)</label>
                    <input type="text"
                           name="passphrase_2"
                           id="passphrase_2"
                           value="{{ old('passphrase_2') }}"
                           class="block w-full px-3 py-2 border @error('passphrase_2') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           placeholder="Optional additional recovery phrase"
                           autocomplete="off">
                    <p class="text-xs text-gray-500">
                        Additional security layer - also store securely if used
                    </p>
                    @error('passphrase_2')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Passphrase Guidelines --}}
                <div class="bg-gray-50 border border-gray-200 rounded p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Passphrase Guidelines:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-gray-600">
                        <div>
                            <p class="font-medium text-green-600 mb-1">Good Practices:</p>
                            <ul class="space-y-0.5 list-disc list-inside">
                                <li>Use 3-5 random words</li>
                                <li>Include spaces or symbols</li>
                                <li>Make it memorable to you</li>
                                <li>Use unique phrases</li>
                                <li>Write it down securely</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-medium text-red-600 mb-1">Avoid:</p>
                            <ul class="space-y-0.5 list-disc list-inside">
                                <li>Personal information</li>
                                <li>Common phrases or quotes</li>
                                <li>Dictionary words in order</li>
                                <li>Dates or names</li>
                                <li>Reusing from other sites</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Recovery Process Info --}}
                <div class="bg-blue-50 border border-blue-200 rounded p-4">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">How Recovery Works:</h4>
                    <ol class="text-sm text-blue-700 space-y-1 list-decimal list-inside">
                        <li>If you lose access to your account, you can use the recovery process</li>
                        <li>You'll need to provide your private username and primary passphrase</li>
                        <li>If set, the secondary passphrase provides additional verification</li>
                        <li>Recovery allows you to reset your password and regain access</li>
                    </ol>
                </div>

                {{-- Storage Instructions --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                    <h4 class="text-sm font-medium text-yellow-800 mb-2">💾 Secure Storage Instructions:</h4>
                    <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                        <li><strong>Write them down</strong> on paper and store in a safe place</li>
                        <li><strong>Use a password manager</strong> with offline backup</li>
                        <li><strong>Store copies</strong> in multiple secure locations</li>
                        <li><strong>Never store</strong> in plain text files or emails</li>
                        <li><strong>Consider encryption</strong> for digital storage</li>
                        <li><strong>Tell a trusted person</strong> where to find them if needed</li>
                    </ul>
                </div>

                {{-- Confirmation Checkbox --}}
                <div class="pt-4 border-t border-gray-100">
                    <label class="flex items-start space-x-3">
                        <input type="checkbox"
                               name="confirm_storage"
                               id="confirm_storage"
                               class="mt-1 w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
                               required>
                        <span class="text-sm text-gray-700">
                            <strong class="text-red-700">I understand that these passphrases are critical for account recovery</strong>
                            and I will store them securely offline. I acknowledge that {{ config('app.name') }} cannot recover
                            lost passphrases and losing them may result in permanent account lockout.
                        </span>
                    </label>
                </div>

                {{-- Submit Actions --}}
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                            class="px-6 py-2 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">
                        Update Recovery Passphrases
                    </button>
                    <a href="{{ route('profile.show') }}"
                       class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Final Warning --}}
        <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-red-800 mb-2">🚨 Final Security Reminder:</h4>
            <div class="text-sm text-red-700 space-y-1">
                <p><strong>BEFORE clicking "Update":</strong></p>
                <ol class="list-decimal list-inside ml-4 space-y-1">
                    <li>Write down your new passphrases on paper</li>
                    <li>Store the paper in a secure location</li>
                    <li>Verify you can read your handwriting clearly</li>
                    <li>Consider making a backup copy</li>
                </ol>
                <p class="font-semibold pt-2">
                    Once you update these passphrases, your old ones will no longer work for recovery!
                </p>
            </div>
        </div>
    </div>
@endsection
