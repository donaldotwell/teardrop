@extends('layouts.auth')
@section('page-title', 'Account Recovery')

@section('page-heading')
    <h1 class="text-2xl font-semibold text-gray-900">Account Recovery</h1>
    <p class="text-gray-600 mt-1">Recover your account using your recovery passphrases</p>
@endsection

@section('content')
    <div class="bg-white p-8 border border-gray-200 rounded-lg">
        <!-- Security Notice -->
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-sm text-amber-800">
                <strong>Security Notice:</strong> You will need your <strong>private username</strong> (not your public username) and recovery passphrases that you set during registration. 
                If you do not have your recovery passphrases, you will not be able to recover your account.
            </p>
        </div>

        <form action="{{ route('recovery.verify') }}" method="post" class="space-y-6" autocomplete="off">
            @csrf

            <div class="space-y-1">
                <label for="username_pri" class="block text-sm font-medium text-gray-700">
                    Private Username <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="username_pri"
                       id="username_pri"
                       value="{{ old('username_pri') }}"
                       class="block w-full px-3 py-2 border @error('username_pri') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                       required
                       autofocus
                       autocomplete="off">
                <p class="text-xs text-gray-500">Enter your private username (the one you use to login, not your public display name)</p>
                @error('username_pri')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="passphrase_1" class="block text-sm font-medium text-gray-700">
                    Primary Recovery Passphrase <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="passphrase_1"
                       id="passphrase_1"
                       class="block w-full px-3 py-2 border @error('passphrase_1') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                       required>
                <p class="text-xs text-gray-500">Enter your primary recovery passphrase (set during registration)</p>
                @error('passphrase_1')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="passphrase_2" class="block text-sm font-medium text-gray-700">
                    Secondary Recovery Passphrase
                </label>
                <input type="text"
                       name="passphrase_2"
                       id="passphrase_2"
                       class="block w-full px-3 py-2 border @error('passphrase_2') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-1 focus:ring-yellow-500">
                <p class="text-xs text-gray-500">Optional: If you set a secondary passphrase, enter it here</p>
                @error('passphrase_2')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @error('error')
            <div class="p-3 bg-red-50 border border-red-200 rounded">
                <p class="text-sm text-red-800">{{ $message }}</p>
            </div>
            @enderror

            <div class="space-y-4">
                <button type="submit"
                        class="w-full py-3 px-4 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700 focus:outline-none focus:bg-yellow-700">
                    Verify Passphrases
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-yellow-700 hover:text-yellow-800">
                        ← Back to Login
                    </a>
                </div>
            </div>
        </form>
    </div>
@endsection
