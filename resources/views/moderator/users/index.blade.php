@extends('layouts.moderator')
@section('page-title', 'User Management')

@section('breadcrumbs')
    <span class="text-gray-600">User Management</span>
@endsection

@section('page-heading')
    User Management
@endsection

@section('page-description')
    Manage users, review accounts, and handle user violations
@endsection

@section('page-actions')
    <div class="flex items-center space-x-3">
        <select name="bulk_action" class="px-3 py-2 border border-gray-300 rounded text-sm">
            <option value="">Bulk Actions</option>
            <option value="ban">Ban Selected</option>
            <option value="unban">Unban Selected</option>
        </select>
        <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
            Apply
        </button>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Filter Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8">
                <a href="{{ route('moderator.users.index') }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ !request('status') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    All Users ({{ $stats['total_users'] }})
                </a>
                <a href="{{ route('moderator.users.index', ['status' => 'active']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'active' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Active ({{ $stats['active_users'] }})
                </a>
                <a href="{{ route('moderator.users.index', ['status' => 'banned']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'banned' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Banned ({{ $stats['banned_users'] }})
                </a>
                <a href="{{ route('moderator.users.index', ['suspicious' => 1]) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('suspicious') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Suspicious ({{ $stats['suspicious_users'] }})
                </a>
            </nav>
        </div>

        {{-- Search and Filters --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <form method="GET" class="flex flex-wrap items-center gap-4">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="suspicious" value="{{ request('suspicious') }}">

                <div class="flex-1 min-w-64">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by username, email, or ID..."
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <select name="trust_level" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Trust Levels</option>
                    <option value="0" {{ request('trust_level') === '0' ? 'selected' : '' }}>Level 0</option>
                    <option value="1" {{ request('trust_level') === '1' ? 'selected' : '' }}>Level 1</option>
                    <option value="2" {{ request('trust_level') === '2' ? 'selected' : '' }}>Level 2</option>
                    <option value="3" {{ request('trust_level') === '3' ? 'selected' : '' }}>Level 3</option>
                </select>

                <select name="vendor_level" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Vendor Levels</option>
                    <option value="0" {{ request('vendor_level') === '0' ? 'selected' : '' }}>Non-Vendor</option>
                    <option value="1" {{ request('vendor_level') === '1' ? 'selected' : '' }}>Basic Vendor</option>
                    <option value="2" {{ request('vendor_level') === '2' ? 'selected' : '' }}>Premium Vendor</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('moderator.users.index') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Clear
                </a>
            </form>
        </div>

        {{-- Users Table --}}
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="w-8 px-6 py-3">
                            <input type="checkbox" class="rounded border-gray-300">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Trust Level
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reports
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Joined
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <input type="checkbox" value="{{ $user->id }}" class="rounded border-gray-300">
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-blue-600 font-medium text-sm">
                                                {{ substr($user->username_pub, 0, 1) }}
                                            </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('profile.show', $user->username_pub) }}"
                                               class="hover:text-blue-600">
                                                {{ $user->username_pub }}
                                            </a>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: {{ $user->id }}
                                            @if($user->vendor_level > 0)
                                                • <span class="text-green-600">Vendor L{{ $user->vendor_level }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $user->status === 'active' ? 'bg-green-100 text-green-800' :
                                           ($user->status === 'banned' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                        <span class="text-blue-600 font-medium text-xs">{{ $user->trust_level }}</span>
                                    </div>
                                    Trust Level {{ $user->trust_level }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <div class="text-gray-900">{{ $user->reports_against_count ?? 0 }} reports</div>
                                    @if(($user->reports_against_count ?? 0) > 3)
                                        <div class="text-red-600 text-xs">High activity</div>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $user->created_at->format('M d, Y') }}
                                <div class="text-xs text-gray-400">
                                    {{ $user->created_at->diffForHumans() }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('profile.show', $user->username_pub) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        View
                                    </a>

                                    @if($user->status === 'active')
                                        <form method="POST" action="{{ route('moderator.forum.moderate.users.ban', $user) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-800 text-sm">
                                                Ban
                                            </button>
                                        </form>
                                    @elseif($user->status === 'banned')
                                        <form method="POST" action="{{ route('moderator.forum.moderate.users.unban', $user) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-green-600 hover:text-green-800 text-sm">
                                                Unban
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No users found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $users->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
