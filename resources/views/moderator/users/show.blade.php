@extends('layouts.moderator')
@section('page-title', 'User Details')

@section('breadcrumbs')
    <a href="{{ route('moderator.users.index') }}" class="text-blue-700 hover:text-blue-800">Users</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">{{ $user->username_pub }}</span>
@endsection

@section('page-heading')
    User Details: {{ $user->username_pub }}
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                {{ session('error') }}
            </div>
        @endif

        {{-- User Overview --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-blue-700 font-bold text-xl">{{ substr($user->username_pub, 0, 1) }}</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $user->username_pub }}</h2>
                        <p class="text-gray-600 text-sm">User ID: {{ $user->id }}</p>
                        <div class="flex items-center space-x-2 mt-2">
                            @if($user->status === 'active')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @elseif($user->status === 'banned')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Banned</span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                            @endif

                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Trust Level {{ $user->trust_level }}
                            </span>

                            @foreach($user->roles as $role)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    {{ $role->name === 'admin' ? 'bg-red-100 text-red-800' :
                                       ($role->name === 'moderator' ? 'bg-purple-100 text-purple-800' :
                                       ($role->name === 'vendor' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Ban/Unban Actions (only for non-admin/non-moderator users) --}}
                @if(!$user->hasAnyRole(['admin', 'moderator']))
                    <div class="flex space-x-2">
                        @if($user->status !== 'banned')
                            <form action="{{ route('moderator.forum.moderate.users.ban', $user) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                    Ban User
                                </button>
                            </form>
                        @else
                            <form action="{{ route('moderator.forum.moderate.users.unban', $user) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                    Unban User
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded text-xs text-gray-600">
                        Protected role — admin action required
                    </div>
                @endif
            </div>

            {{-- User Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-gray-100">
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900">{{ $user->orders_count ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Total Orders</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-green-600">{{ $user->completed_orders_count ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Completed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-red-600">{{ $user->reports_against_count ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Reports Against</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-blue-600">{{ $user->vendor_level }}</div>
                    <div class="text-sm text-gray-600">Vendor Level</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Account Information --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Public Username:</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $user->username_pub }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Status:</dt>
                        <dd class="text-sm font-medium text-gray-900 capitalize">{{ $user->status }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Trust Level:</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $user->trust_level }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Vendor Level:</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $user->vendor_level }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Joined:</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $user->created_at->format('M d, Y g:i A') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Last Login:</dt>
                        <dd class="text-sm font-medium text-gray-900">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y g:i A') : 'Never' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Last Seen:</dt>
                        <dd class="text-sm font-medium text-gray-900">
                            {{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never' }}
                        </dd>
                    </div>
                    @if($user->vendor_since)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Vendor Since:</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $user->vendor_since->format('M d, Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Forum Activity --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Forum Activity</h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Forum Posts:</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $user->forum_posts_count ?? 0 }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Forum Comments:</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $user->forum_comments_count ?? 0 }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Reports Against:</dt>
                        <dd class="text-sm font-medium {{ ($user->reports_against_count ?? 0) > 3 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $user->reports_against_count ?? 0 }}
                            @if(($user->reports_against_count ?? 0) > 3)
                                (High)
                            @endif
                        </dd>
                    </div>
                </dl>

                @if($user->hasRole('vendor'))
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Vendor Info</h4>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Vendor Level:</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->vendor_level }}</dd>
                            </div>
                            @if($user->early_finalization_enabled)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Early Finalization:</dt>
                                    <dd class="text-sm font-medium text-green-600">Enabled</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif
            </div>
        </div>

        {{-- Roles --}}
        @if($user->roles->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">User Roles</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($user->roles as $role)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $role->name === 'admin' ? 'bg-red-100 text-red-800' :
                               ($role->name === 'moderator' ? 'bg-purple-100 text-purple-800' :
                               ($role->name === 'vendor' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800')) }}">
                            {{ ucfirst($role->name) }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Back Link --}}
        <div class="flex">
            <a href="{{ route('moderator.users.index') }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm">
                Back to Users
            </a>
        </div>
    </div>
@endsection
