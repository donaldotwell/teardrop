@extends('layouts.auth')
@section('page-title', 'Reset Password')

@section('page-heading')
    <h1 class="text-2xl font-semibold text-gray-900">Reset Your Password</h1>
    <p class="text-gray-600 mt-1">Create a new password for your account</p>
@endsection

@section('content')
    <div class="bg-white p-8 border border-gray-200 rounded-lg">
        <!-- Success Message -->
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-800">
                <strong>Passphrases verified!</strong> You can now set a new password for your account.
            </p>
        </div>

        <!-- Security Notice -->
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-sm text-amber-800">
                <strong>Session expires in 15 minutes.</strong> Please set your new password before the session expires.
            </p>
        </div>

        <form action="{{ route('recovery.reset-password.submit') }}" method="post" class="space-y-6" autocomplete="off">
            @csrf

            <div class="space-y-1">
                <label for="password" class="block text-sm font-medium text-gray-700">
                    New Password <span class="text-red-500">*</span>
                </label>
                <input type="password"
                       name="password"
                       id="password"
                       class="block w-full px-3 py-2 border @error('password') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                       required
                       autofocus>
                <p class="text-xs text-gray-500">
                    Minimum 8 characters with uppercase, lowercase, numbers, and symbols
                </p>
                @error('password')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Confirm Password <span class="text-red-500">*</span>
                </label>
                <input type="password"
                       name="password_confirmation"
                       id="password_confirmation"
                       class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                       required>
            </div>

            <div class="space-y-4">
                <button type="submit"
                        class="w-full py-3 px-4 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700 focus:outline-none focus:bg-yellow-700">
                    Reset Password
                </button>
            </div>
        </form>
    </div>
@endsection
