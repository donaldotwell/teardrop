@extends('layouts.app')
@section('page-title', 'Change Password')

@section('breadcrumbs')
    <a href="{{ route('profile.show') }}" class="text-amber-700 hover:text-amber-900">Profile</a>
    <span class="text-amber-400">/</span>
    <span class="text-amber-700">Change Password</span>
@endsection

@section('page-heading')
    Change Password
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">

        {{-- Security Notice --}}
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h3 class="text-sm font-medium text-blue-800 mb-1">Password Security</h3>
            <p class="text-sm text-blue-700">
                Use a strong, unique password that you don't use anywhere else.
                Your password should contain uppercase and lowercase letters, numbers, and symbols.
            </p>
        </div>

        {{-- Password Change Form --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form action="{{ route('profile.password.update') }}" method="post" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Current Password --}}
                <div class="space-y-1">
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password *</label>
                    <input type="password"
                           name="current_password"
                           id="current_password"
                           class="block w-full px-3 py-2 border @error('current_password') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           autocomplete="current-password"
                           required>
                    <p class="text-xs text-gray-500">Enter your current password to confirm changes</p>
                    @error('current_password')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Password --}}
                <div class="space-y-1">
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password *</label>
                    <input type="password"
                           name="password"
                           id="password"
                           class="block w-full px-3 py-2 border @error('password') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           autocomplete="new-password"
                           required>
                    <p class="text-xs text-gray-500">
                        Minimum 8 characters with uppercase, lowercase, numbers, and symbols
                    </p>
                    @error('password')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm New Password --}}
                <div class="space-y-1">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password *</label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           autocomplete="new-password"
                           required>
                    <p class="text-xs text-gray-500">Re-enter your new password</p>
                </div>

                {{-- Password Requirements --}}
                <div class="bg-gray-50 border border-gray-200 rounded p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Password Requirements:</h4>
                    <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                        <li>At least 8 characters long</li>
                        <li>Contains uppercase letters (A-Z)</li>
                        <li>Contains lowercase letters (a-z)</li>
                        <li>Contains numbers (0-9)</li>
                        <li>Contains symbols (!@#$%^&*)</li>
                        <li>Not previously compromised in data breaches</li>
                    </ul>
                </div>

                {{-- Submit Actions --}}
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                            class="px-6 py-2 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">
                        Update Password
                    </button>
                    <a href="{{ route('profile.show') }}"
                       class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Security Tips --}}
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-yellow-800 mb-2">Security Tips:</h4>
            <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                <li>Never share your password with anyone</li>
                <li>Use a unique password that you don't use on other sites</li>
                <li>Consider using a password manager</li>
                <li>Change your password if you suspect it's been compromised</li>
            </ul>
        </div>
    </div>
@endsection
