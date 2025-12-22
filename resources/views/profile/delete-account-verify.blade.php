@extends('layouts.app')
@section('page-title', 'Verify Account Deletion')

@section('breadcrumbs')
    <a href="{{ route('profile.show') }}" class="text-yellow-600 hover:text-yellow-700">Profile</a>
    <span class="text-gray-600 mx-2">/</span>
    <a href="{{ route('profile.delete-account.show') }}" class="text-yellow-600 hover:text-yellow-700">Delete Account</a>
    <span class="text-gray-600 mx-2">/</span>
    <span class="text-gray-600">Verify</span>
@endsection

@section('page-heading')
    Final Verification - Decrypt PGP Challenge
@endsection

@section('content')
    <div class="max-w-3xl mx-auto">

        {{-- Final Warning --}}
        <div class="bg-red-50 border-2 border-red-400 rounded-lg p-6 mb-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="text-red-600 text-2xl">⚠</div>
                <h2 class="text-lg font-bold text-red-900">Final Step: Account Will Be Permanently Deleted</h2>
            </div>
            <p class="text-red-800 font-medium">
                This is your last chance to cancel. Once you submit the correct verification code,
                your account will be <strong>immediately and permanently deleted</strong> with no possibility of recovery.
            </p>
        </div>

        {{-- Instructions --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Decryption Instructions</h3>

            <div class="space-y-4 text-sm text-gray-700">
                <div>
                    <p class="font-medium mb-2">Step 1: Copy the encrypted message below</p>
                    <p class="text-gray-600">
                        Select and copy the entire PGP message block including the BEGIN and END markers.
                    </p>
                </div>

                <div>
                    <p class="font-medium mb-2">Step 2: Decrypt with your private key</p>
                    <p class="text-gray-600 mb-2">
                        Use your PGP client (GPG, Kleopatra, Mailvelope, etc.) to decrypt the message
                        using your private key associated with the public key on your account.
                    </p>
                    <div class="bg-gray-50 border border-gray-200 rounded p-3 font-mono text-xs">
                        <p class="text-gray-700">Command line example:</p>
                        <p class="text-gray-900 mt-1">echo "ENCRYPTED_MESSAGE" | gpg --decrypt</p>
                    </div>
                </div>

                <div>
                    <p class="font-medium mb-2">Step 3: Submit the verification code</p>
                    <p class="text-gray-600">
                        The decrypted message will contain a verification code. Enter it in the form below
                        to permanently delete your account.
                    </p>
                </div>
            </div>
        </div>

        {{-- Encrypted Challenge Message --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Encrypted Challenge</h3>

            <pre class="bg-gray-900 text-green-400 p-4 rounded text-xs overflow-x-auto border border-gray-700">{{ $encryptedMessage }}</pre>
            <p class="text-xs text-gray-600 mt-2">Select and copy the entire message above including BEGIN and END markers</p>
        </div>

        {{-- Verification Form --}}
        <div class="bg-white border-2 border-red-300 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-900 mb-4">Submit Verification Code</h3>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded p-4 mb-6">
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-800">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('profile.delete-account.confirm') }}" method="post" class="space-y-6">
                @csrf

                <div>
                    <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Verification Code from Decrypted Message <span class="text-red-600">*</span>
                    </label>
                    <input type="text"
                           name="verification_code"
                           id="verification_code"
                           class="block w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 font-mono text-lg text-center uppercase"
                           placeholder="ENTER CODE HERE"
                           required
                           autofocus>
                    <p class="text-xs text-gray-600 mt-2">
                        Enter the verification code exactly as shown in the decrypted message.
                        You have 5 attempts before the challenge expires.
                    </p>
                </div>

                <div class="bg-red-50 border border-red-300 rounded p-4">
                    <p class="text-sm text-red-900 font-semibold mb-2">
                        ⚠ By submitting this code, you confirm:
                    </p>
                    <ul class="text-xs text-red-800 space-y-1">
                        <li>• Your account will be immediately and permanently deleted</li>
                        <li>• All data, wallets, orders, and messages will be erased</li>
                        <li>• This action cannot be undone or reversed</li>
                        <li>• You will be logged out and cannot access this account again</li>
                    </ul>
                </div>

                <div class="flex gap-4">
                    <button type="submit"
                            class="flex-1 px-6 py-3 bg-red-600 text-white font-bold rounded hover:bg-red-700 transition">
                        DELETE MY ACCOUNT PERMANENTLY
                    </button>
                    <a href="{{ route('profile.show') }}"
                       class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded hover:bg-gray-300 transition text-center">
                        Cancel Deletion
                    </a>
                </div>
            </form>
        </div>

        {{-- Expiration Notice --}}
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                This verification challenge will expire in <strong>30 minutes</strong>.
                You have <strong>5 attempts</strong> to enter the correct code.
            </p>
        </div>
    </div>
@endsection
