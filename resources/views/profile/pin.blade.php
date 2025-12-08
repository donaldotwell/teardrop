@extends('layouts.app')
@section('page-title', 'Change Security PIN')

@section('breadcrumbs')
    <a href="{{ route('profile.show') }}" class="text-yellow-700 hover:text-yellow-800">Profile</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Change PIN</span>
@endsection

@section('page-heading')
    Change Security PIN
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">

        {{-- PIN Security Notice --}}
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <h3 class="text-sm font-medium text-red-800 mb-1">Critical Security Setting</h3>
            <p class="text-sm text-red-700">
                Your PIN protects wallet transactions and sensitive account actions.
                Choose a PIN that's not easily guessable and don't use common patterns like 123456 or your birthday.
            </p>
        </div>

        {{-- PIN Change Form --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form action="{{ route('profile.pin.update') }}" method="post" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Current PIN --}}
                <div class="space-y-1">
                    <label for="current_pin" class="block text-sm font-medium text-gray-700">Current PIN *</label>
                    <input type="password"
                           name="current_pin"
                           id="current_pin"
                           class="block w-full px-3 py-2 border @error('current_pin') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           placeholder="000000"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           autocomplete="off"
                           required>
                    <p class="text-xs text-gray-500">Enter your current 6-digit PIN</p>
                    @error('current_pin')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New PIN --}}
                <div class="space-y-1">
                    <label for="pin" class="block text-sm font-medium text-gray-700">New PIN *</label>
                    <input type="password"
                           name="pin"
                           id="pin"
                           class="block w-full px-3 py-2 border @error('pin') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           placeholder="000000"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           autocomplete="off"
                           required>
                    <p class="text-xs text-gray-500">Choose a secure 6-digit PIN</p>
                    @error('pin')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm New PIN --}}
                <div class="space-y-1">
                    <label for="pin_confirmation" class="block text-sm font-medium text-gray-700">Confirm New PIN *</label>
                    <input type="password"
                           name="pin_confirmation"
                           id="pin_confirmation"
                           class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                           placeholder="000000"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           autocomplete="off"
                           required>
                    <p class="text-xs text-gray-500">Re-enter your new PIN</p>
                </div>

                {{-- PIN Guidelines --}}
                <div class="bg-gray-50 border border-gray-200 rounded p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">PIN Security Guidelines:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-gray-600">
                        <div>
                            <p class="font-medium text-red-600 mb-1">Avoid:</p>
                            <ul class="space-y-0.5 list-disc list-inside">
                                <li>123456, 000000, 111111</li>
                                <li>Your birthday or anniversary</li>
                                <li>Repeating patterns (112233)</li>
                                <li>Sequential numbers (234567)</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-medium text-green-600 mb-1">Better:</p>
                            <ul class="space-y-0.5 list-disc list-inside">
                                <li>Random digit combinations</li>
                                <li>Mix of different numbers</li>
                                <li>Something memorable to you only</li>
                                <li>Not related to personal info</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- PIN Usage Notice --}}
                <div class="bg-blue-50 border border-blue-200 rounded p-4">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">Your PIN is used for:</h4>
                    <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                        <li>Cryptocurrency wallet transactions</li>
                        <li>Withdrawing funds</li>
                        <li>Changing security settings</li>
                        <li>Accessing sensitive account features</li>
                    </ul>
                </div>

                {{-- Submit Actions --}}
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                            class="px-6 py-2 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">
                        Update PIN
                    </button>
                    <a href="{{ route('profile.show') }}"
                       class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Security Reminder --}}
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-yellow-800 mb-2">🔐 Important Reminders:</h4>
            <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                <li>Never share your PIN with anyone</li>
                <li>Don't write your PIN down in an obvious place</li>
                <li>Change your PIN if you suspect it's been compromised</li>
                <li>Use a different PIN than your phone or bank cards</li>
            </ul>
        </div>
    </div>
@endsection
