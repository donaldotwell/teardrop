@extends('layouts.app')
@section('page-title', 'Profile Settings')

@section('breadcrumbs')
    <span class="text-amber-700">Profile Settings</span>
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

            {{-- Activity Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div class="text-sm">
                    <span class="font-medium text-gray-700">Last Login:</span>
                    <span class="text-gray-600">
                        @if($user->last_login_at)
                            {{ $user->last_login_at->diffForHumans() }}
                        @else
                            Never
                        @endif
                    </span>
                </div>
                <div class="text-sm">
                    <span class="font-medium text-gray-700">Last Activity:</span>
                    <span class="text-gray-600">
                        @if($user->last_seen_at)
                            {{ $user->last_seen_at->diffForHumans() }}
                        @else
                            Never
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- General Information --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">General Information</h3>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Private Username</label>
                        <input type="text"
                               value="{{ $user->username_pri }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded bg-gray-50"
                               disabled>
                        <p class="text-xs text-gray-500">Cannot be changed for security reasons</p>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Public Username</label>
                        <input type="text"
                               value="{{ $user->username_pub }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded bg-gray-50"
                               disabled>
                        <p class="text-xs text-gray-500">Cannot be changed for security reasons</p>
                    </div>
                </div>

                {{-- PGP Key Section --}}
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="font-medium text-gray-900">PGP Public Key</h4>
                            <p class="text-sm text-gray-600">For encrypted communications</p>
                        </div>
                        <a href="{{ route('profile.pgp') }}"
                           class="px-4 py-2 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700 text-sm">
                            {{ $user->pgp_pub_key ? 'Update Key' : 'Add Key' }}
                        </a>
                    </div>

                    @if($user->pgp_pub_key)
                        <div class="bg-gray-50 border border-gray-200 rounded p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                                <span class="text-sm font-medium text-gray-900">Key Configured</span>
                            </div>
                            <pre class="text-xs text-gray-700 overflow-x-auto mt-2 p-2 bg-white rounded border border-gray-200">{{ Str::limit($user->pgp_pub_key, 300) }}</pre>
                        </div>
                    @else
                        <div class="bg-blue-50 border border-blue-200 rounded p-4">
                            <p class="text-sm text-blue-800">
                                No PGP key configured. Add a PGP public key to enable encrypted communications with vendors and support.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
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
                                {{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never' }}
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
