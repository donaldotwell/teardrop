@extends('layouts.app')
@section('page-title', 'Profile Settings')

@section('breadcrumbs')
    <span class="text-gray-600">Profile Settings</span>
@endsection

@section('page-heading')
    Profile Settings
@endsection

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Profile Overview Card --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center space-x-4 mb-6">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                    <span class="text-yellow-700 font-bold text-xl">{{ substr($user->username_pub, 0, 1) }}</span>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $user->username_pub }}</h2>
                    <p class="text-gray-600">Trust Level {{ $user->trust_level }}</p>
                    <p class="text-sm text-gray-500">Member since {{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            {{-- Account Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-gray-100">
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $user->trust_level }}</div>
                    <div class="text-sm text-gray-600">Trust Level</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $user->vendor_level }}</div>
                    <div class="text-sm text-gray-600">Vendor Level</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $user->orders()->count() }}</div>
                    <div class="text-sm text-gray-600">Orders</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $user->status }}</div>
                    <div class="text-sm text-gray-600">Status</div>
                </div>
            </div>
        </div>

        {{-- General Information --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">General Information</h3>

            <form action="{{ route('profile.update') }}" method="post" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="private_username" class="block text-sm font-medium text-gray-700">Private Username</label>
                        <input type="text"
                               id="private_username"
                               value="{{ $user->username_pri }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded bg-gray-50"
                               disabled>
                        <p class="text-xs text-gray-500">Cannot be changed for security reasons</p>
                    </div>

                    <div class="space-y-1">
                        <label for="public_username" class="block text-sm font-medium text-gray-700">Public Username</label>
                        <input type="text"
                               name="public_username"
                               id="public_username"
                               value="{{ old('public_username', $user->username_pub) }}"
                               class="block w-full px-3 py-2 border @error('public_username') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                               required>
                        <p class="text-xs text-gray-500">Displayed to other users</p>
                        @error('public_username')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="pgp_pub_key" class="block text-sm font-medium text-gray-700">PGP Public Key</label>
                    <textarea name="pgp_pub_key"
                              id="pgp_pub_key"
                              rows="8"
                              class="block w-full px-3 py-2 border @error('pgp_pub_key') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 font-mono text-sm"
                              placeholder="-----BEGIN PGP PUBLIC KEY BLOCK-----&#10;&#10;-----END PGP PUBLIC KEY BLOCK-----">{{ old('pgp_pub_key', $user->pgp_pub_key) }}</textarea>
                    <p class="text-xs text-gray-500">Optional: For encrypted communications</p>
                    @error('pgp_pub_key')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button type="submit"
                            class="px-6 py-2 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>

        {{-- Security Settings Links --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Security Settings</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('profile.password.show') }}"
                   class="block p-4 border border-gray-200 rounded-lg hover:border-yellow-300 hover:bg-yellow-50">
                    <div class="font-medium text-gray-900 mb-1">Password</div>
                    <div class="text-sm text-gray-600">Change your login password</div>
                </a>

                <a href="{{ route('profile.pin.show') }}"
                   class="block p-4 border border-gray-200 rounded-lg hover:border-yellow-300 hover:bg-yellow-50">
                    <div class="font-medium text-gray-900 mb-1">Security PIN</div>
                    <div class="text-sm text-gray-600">Update your 6-digit PIN</div>
                </a>

                <a href="{{ route('profile.passphrases.show') }}"
                   class="block p-4 border border-gray-200 rounded-lg hover:border-yellow-300 hover:bg-yellow-50">
                    <div class="font-medium text-gray-900 mb-1">Recovery Passphrases</div>
                    <div class="text-sm text-gray-600">Manage recovery phrases</div>
                </a>
            </div>
        </div>

        {{-- Account Information --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Account Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Account Details</h4>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Account Created:</dt>
                            <dd class="text-gray-900">{{ $user->created_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Last Login:</dt>
                            <dd class="text-gray-900">
                                {{ $user->last_login_at ? $user->last_login_at->format('M d, Y g:i A') : 'Never' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Last Seen:</dt>
                            <dd class="text-gray-900">
                                {{ $user->last_seen ? $user->last_seen->diffForHumans() : 'Never' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Vendor Information</h4>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Vendor Level:</dt>
                            <dd class="text-gray-900">{{ $user->vendor_level }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Vendor Since:</dt>
                            <dd class="text-gray-900">
                                {{ $user->vendor_since ? $user->vendor_since->format('M d, Y') : 'Not a vendor' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Account Status:</dt>
                            <dd class="text-gray-900 capitalize">{{ $user->status }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
