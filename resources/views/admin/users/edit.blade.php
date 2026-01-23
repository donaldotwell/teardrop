@extends('layouts.admin')
@section('page-title', 'Edit User')

@section('breadcrumbs')
    <a href="{{ route('admin.users.index') }}" class="text-yellow-700 hover:text-yellow-800">Users</a>
    <span class="text-gray-400">/</span>
    <a href="{{ route('admin.users.show', $user) }}" class="text-yellow-700 hover:text-yellow-800">{{ $user->username_pub }}</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Edit</span>
@endsection

@section('page-heading')
    Edit User: {{ $user->username_pub }}
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">

        {{-- User Info Header --}}
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <span class="text-yellow-700 font-bold text-lg">{{ substr($user->username_pub, 0, 1) }}</span>
                </div>
                <div>
                    <h3 class="font-medium text-yellow-800">Editing: {{ $user->username_pub }}</h3>
                    <p class="text-sm text-yellow-700">User ID: {{ $user->id }} • Member since {{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Edit Form --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form action="{{ route('admin.users.update', $user) }}" method="post" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Basic Information --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="username_pri" class="block text-sm font-medium text-gray-700">Private Username</label>
                            <input type="text"
                                   id="username_pri"
                                   value="{{ $user->username_pri }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded bg-gray-50"
                                   disabled>
                            <p class="text-xs text-gray-500">Cannot be changed for security reasons</p>
                        </div>

                        <div class="space-y-1">
                            <label for="username_pub" class="block text-sm font-medium text-gray-700">Public Username</label>
                            <input type="text"
                                   name="username_pub"
                                   id="username_pub"
                                   value="{{ old('username_pub', $user->username_pub) }}"
                                   class="block w-full px-3 py-2 border @error('username_pub') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                   required>
                            @error('username_pub')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Trust and Vendor Levels --}}
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Trust & Vendor Settings</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="trust_level" class="block text-sm font-medium text-gray-700">Trust Level</label>
                            <select name="trust_level"
                                    id="trust_level"
                                    class="block w-full px-3 py-2 border @error('trust_level') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                    required>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('trust_level', $user->trust_level) == $i ? 'selected' : '' }}>
                                        Level {{ $i }}
                                    </option>
                                @endfor
                            </select>
                            <p class="text-xs text-gray-500">Higher levels indicate more trusted users</p>
                            @error('trust_level')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="vendor_level" class="block text-sm font-medium text-gray-700">Vendor Level</label>
                            <select name="vendor_level"
                                    id="vendor_level"
                                    class="block w-full px-3 py-2 border @error('vendor_level') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                    required>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('vendor_level', $user->vendor_level) == $i ? 'selected' : '' }}>
                                        {{ $i === 0 ? 'Not a Vendor' : "Level {$i}" }}
                                    </option>
                                @endfor
                            </select>
                            <p class="text-xs text-gray-500">0 = Regular user, 1+ = Vendor with selling privileges</p>
                            @error('vendor_level')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Account Status --}}
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Status</h3>

                    <div class="space-y-1">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status"
                                id="status"
                                class="block w-full px-3 py-2 border @error('status') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                required>
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>
                                Active - Full access to the platform
                            </option>
                            <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>
                                Inactive - Limited access
                            </option>
                            <option value="banned" {{ old('status', $user->status) === 'banned' ? 'selected' : '' }}>
                                Banned - No access to the platform
                            </option>
                        </select>
                        @error('status')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Additional Information --}}
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Account Statistics</h4>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Total Orders:</dt>
                                    <dd class="font-medium text-gray-900">{{ $user->orders->count() }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Completed Orders:</dt>
                                    <dd class="font-medium text-gray-900">{{ $user->orders->where('status', 'completed')->count() }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Total Spent:</dt>
                                    <dd class="font-medium text-gray-900">${{ number_format($user->orders->where('status', 'completed')->sum('usd_price'), 2) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Account Timeline</h4>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Created:</dt>
                                    <dd class="font-medium text-gray-900">{{ $user->created_at->format('M d, Y') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Last Login:</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ $user->last_login_at ? $user->last_login_at->format('M d, Y') : 'Never' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Last Seen:</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Submit Actions --}}
                <div class="border-t border-gray-100 pt-6 flex gap-3">
                    <button type="submit"
                            class="px-6 py-2 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">
                        Update User
                    </button>
                    <a href="{{ route('admin.users.show', $user) }}"
                       class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Danger Zone --}}
        <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-800 mb-4">Danger Zone</h3>
            <p class="text-sm text-red-700 mb-4">
                These actions are irreversible. Please be certain before proceeding.
            </p>

            <div class="flex gap-3">
                @if($user->status !== 'banned')
                    <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Ban User
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Unban User
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
