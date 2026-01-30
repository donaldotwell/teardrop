@extends('layouts.app')
@section('page-title', 'Delete Account')

@section('breadcrumbs')
    <a href="{{ route('profile.show') }}" class="text-amber-700 hover:text-amber-900">Profile</a>
    <span class="text-amber-400">/</span>
    <span class="text-amber-700">Delete Account</span>
@endsection

@section('page-heading')
    Delete Account
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">

        {{-- Warning Banner --}}
        <div class="bg-red-50 border-2 border-red-300 rounded-lg p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <h2 class="text-xl font-bold text-red-900">Warning: Permanent Account Deletion</h2>
            </div>
            <p class="text-red-800 font-medium mb-3">
                This action is <strong>PERMANENT</strong> and <strong>IRREVERSIBLE</strong>. Once confirmed, there is no way to recover your account.
            </p>
            <ul class="text-sm text-red-700 space-y-2">
                <li class="flex items-start gap-2">
                    <span class="text-red-600 font-bold">•</span>
                    <span>Your account and all personal information will be permanently deleted</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-red-600 font-bold">•</span>
                    <span>All cryptocurrency wallet balances will be lost (withdraw funds first!)</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-red-600 font-bold">•</span>
                    <span>Order history, listings, and transaction records will be deleted</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-red-600 font-bold">•</span>
                    <span>All forum posts, comments, and private messages will be removed</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-red-600 font-bold">•</span>
                    <span>Active orders and disputes must be resolved before deletion</span>
                </li>
            </ul>
        </div>

        {{-- Pre-Deletion Checklist --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Before You Delete Your Account</h3>

            <div class="space-y-3 text-sm">
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="check1" class="mt-1">
                    <label for="check1" class="text-gray-700">
                        I have withdrawn all cryptocurrency balances from my wallets
                    </label>
                </div>
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="check2" class="mt-1">
                    <label for="check2" class="text-gray-700">
                        I have resolved or cancelled all active orders and disputes
                    </label>
                </div>
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="check3" class="mt-1">
                    <label for="check3" class="text-gray-700">
                        I have saved any important messages or information
                    </label>
                </div>
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="check4" class="mt-1">
                    <label for="check4" class="text-gray-700">
                        I understand this action is permanent and cannot be undone
                    </label>
                </div>
            </div>
        </div>

        {{-- Deletion Confirmation Form --}}
        <div class="bg-white border-2 border-red-300 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Account Deletion</h3>

            <p class="text-sm text-gray-700 mb-6">
                To proceed, you must verify your identity with your password and confirm by typing
                the exact phrase below. After submission, you will receive an encrypted PGP challenge
                that you must decrypt to finalize the deletion.
            </p>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded p-4 mb-6">
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-800">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('profile.delete-account.process') }}" method="post" class="space-y-6">
                @csrf

                {{-- Password Confirmation --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Current Password <span class="text-red-600">*</span>
                    </label>
                    <input type="password"
                           name="password"
                           id="password"
                           class="block w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500"
                           placeholder="Enter your password"
                           required>
                    <p class="text-xs text-gray-600 mt-1">
                        Your password is required to verify your identity
                    </p>
                </div>

                {{-- Confirmation Text --}}
                <div>
                    <label for="confirmation_text" class="block text-sm font-medium text-gray-700 mb-2">
                        Type: <span class="font-mono font-bold text-red-600">DELETE MY ACCOUNT</span>
                    </label>
                    <input type="text"
                           name="confirmation_text"
                           id="confirmation_text"
                           class="block w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 font-mono"
                           placeholder="DELETE MY ACCOUNT"
                           required>
                    <p class="text-xs text-gray-600 mt-1">
                        Type exactly as shown above (case sensitive)
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-4 pt-4">
                    <button type="submit"
                            class="flex-1 px-6 py-3 bg-red-600 text-white font-semibold rounded hover:bg-red-700 transition">
                        Proceed to PGP Verification
                    </button>
                    <a href="{{ route('profile.show') }}"
                       class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded hover:bg-gray-300 transition text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Info Note --}}
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500">
                After password verification, you will receive a PGP-encrypted challenge that must be
                decrypted with your private key to complete the deletion.
            </p>
        </div>
    </div>
@endsection
