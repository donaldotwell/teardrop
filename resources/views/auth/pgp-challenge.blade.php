@extends('layouts.auth')
@section('page-title', 'PGP Verification')

@section('page-heading')
    <h1 class="text-2xl font-semibold text-gray-900">PGP Login Verification</h1>
    <p class="text-gray-600 mt-1">Decrypt the challenge to complete sign-in</p>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">

        {{-- Status Banner --}}
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-8 h-8 bg-amber-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                    2
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-amber-900 mb-1">Two-Factor Authentication</h3>
                    <p class="text-sm text-amber-800">
                        Your account is protected with PGP verification.
                        Decrypt the message below using your private key and submit the verification code.
                    </p>
                </div>
            </div>
        </div>

        {{-- Challenge Info --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="text-center p-3 bg-gray-50 rounded">
                    <div class="text-xs text-gray-600 mb-1">Expires In</div>
                    <div class="font-semibold text-gray-900">10 minutes</div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded">
                    <div class="text-xs text-gray-600 mb-1">Attempts Remaining</div>
                    <div class="font-semibold text-gray-900">{{ $attemptsRemaining }} / 5</div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900 mb-3">Encrypted Challenge Message</h3>
                <p class="text-sm text-gray-600 mb-3">
                    Select and copy this entire message, then decrypt it with your PGP private key:
                </p>

                <div class="bg-amber-50 border-2 border-amber-300 rounded-lg p-4">
                    <pre class="bg-gray-900 text-amber-300 p-4 rounded-lg overflow-x-auto text-xs leading-relaxed border border-amber-600 select-all">{{ $encryptedMessage }}</pre>
                </div>

                <div class="mt-4 bg-amber-50 border border-amber-200 rounded p-3">
                    <h4 class="text-sm font-semibold text-amber-900 mb-2">How to Decrypt:</h4>
                    <ol class="text-xs text-amber-800 space-y-1 list-decimal list-inside">
                        <li>Select and copy the encrypted message above</li>
                        <li>Save it to a file (e.g., <code class="bg-amber-100 px-1 rounded">challenge.asc</code>)</li>
                        <li>Decrypt using GPG: <code class="bg-amber-100 px-1 rounded">gpg --decrypt challenge.asc</code></li>
                        <li>Look for the line starting with "Verification Code:"</li>
                        <li>Enter the code in the form below</li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- Verification Code Form --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Submit Verification Code</h3>

            <form action="{{ route('login.pgp-challenge.verify') }}" method="post" class="space-y-4" autocomplete="off">
                @csrf

                <div class="space-y-2">
                    <label for="verification_code" class="block text-sm font-medium text-gray-700">
                        Verification Code <span class="text-red-600">*</span>
                    </label>
                    <input type="text"
                           name="verification_code"
                           id="verification_code"
                           class="block w-full px-4 py-3 border @error('verification_code') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 font-mono text-lg uppercase tracking-wider"
                           placeholder="Enter code from decrypted message"
                           maxlength="20"
                           value="{{ old('verification_code') }}"
                           autocomplete="off"
                           autofocus
                           required>

                    <p class="text-xs text-gray-500">
                        Enter the verification code exactly as shown in the decrypted message (case-insensitive).
                    </p>

                    @error('verification_code')
                        <p class="text-sm text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                @if($attemptsRemaining < 5)
                    <div class="bg-red-50 border border-red-200 rounded p-3">
                        <p class="text-sm text-red-800">
                            <strong>Warning:</strong> You have {{ $attemptsRemaining }} attempt(s) remaining.
                            After 5 failed attempts, you will need to log in again.
                        </p>
                    </div>
                @endif

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit"
                            class="w-full py-3 px-4 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700 focus:outline-none focus:bg-yellow-700">
                        Verify and Sign In
                    </button>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-yellow-700 hover:text-yellow-800">
                        Cancel and return to login
                    </a>
                </div>
            </form>
        </div>

        {{-- Help Section --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Alternative Decryption Methods</h3>

            <div class="space-y-4 text-sm">
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Command Line (Linux/Mac/WSL)</h4>
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-gray-600 mb-1">From file:</p>
                            <code class="block bg-gray-100 px-3 py-2 rounded text-xs">gpg --decrypt challenge.asc</code>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Kleopatra (Windows)</h4>
                    <ol class="text-xs text-gray-700 space-y-1 list-decimal list-inside ml-4">
                        <li>Copy the encrypted message</li>
                        <li>Open Kleopatra and click "Decrypt/Verify"</li>
                        <li>Paste the message and enter your passphrase</li>
                        <li>Copy the verification code from the decrypted text</li>
                    </ol>
                </div>
            </div>
        </div>

    </div>
@endsection
