@extends('layouts.auth')
@section('page-title', 'Register')

@section('page-heading')
    <h1 class="text-2xl font-semibold text-gray-900">Create Account</h1>
    <p class="text-gray-600 mt-1">Join {{ config('app.name') }} today</p>
@endsection

@section('content')
    <div class="bg-white p-8 border border-gray-200 rounded-lg">

        {{-- Info Notice --}}
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="text-sm font-medium text-yellow-800 mb-1">Quick Registration</h3>
            <p class="text-sm text-yellow-700">Create your account now. You can complete your security settings after logging in.</p>
        </div>

        <form action="{{ route('register') }}" method="post" class="space-y-6" autocomplete="off">
            @csrf

            {{-- Username Fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="private_username" class="block text-sm font-medium text-gray-700">Private Username *</label>
                    <input type="text"
                           name="private_username"
                           id="private_username"
                           value="{{ old('private_username') }}"
                           class="block w-full px-3 py-2 border @error('private_username') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           autocomplete="nope-private-username"
                           required>
                    <p class="text-xs text-gray-500">Used for secure login (alphanumeric only)</p>
                    @error('private_username')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="public_username" class="block text-sm font-medium text-gray-700">Public Username *</label>
                    <input type="text"
                           name="public_username"
                           id="public_username"
                           value="{{ old('public_username') }}"
                           class="block w-full px-3 py-2 border @error('public_username') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           autocomplete="nope-public-username"
                           required>
                    <p class="text-xs text-gray-500">Displayed to other users (alphanumeric only)</p>
                    @error('public_username')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Password Fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                    <input type="password"
                           name="password"
                           id="password"
                           class="block w-full px-3 py-2 border @error('password') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           autocomplete="new-password"
                           required>
                    <p class="text-xs text-gray-500">Min 8 chars, mixed case, numbers & symbols</p>
                    @error('password')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           autocomplete="new-password"
                           required>
                    <p class="text-xs text-gray-500">Re-enter your password</p>
                </div>
            </div>

            {{-- Terms Notice --}}
            <div class="pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-600">
                    By creating an account, you agree to our Terms of Service and Privacy Policy.
                    You will be prompted to set up additional security measures after registration.
                </p>
            </div>

            {{-- Submit Actions --}}
            <div class="space-y-4">
                <button type="submit"
                        class="w-full py-3 px-4 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">
                    Create Account
                </button>

                <div class="text-center">
                    <span class="text-sm text-gray-600">Already have an account? </span>
                    <a href="{{ route('login') }}" class="text-sm text-yellow-700 hover:text-yellow-800">
                        Sign in here
                    </a>
                </div>
            </div>
        </form>
    </div>
@endsection
