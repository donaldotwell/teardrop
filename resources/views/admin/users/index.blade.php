@extends('layouts.admin')
@section('page-title', 'Users Management')

@section('breadcrumbs')
    <span class="text-gray-600">Users</span>
@endsection

@section('page-heading')
    Users Management
@endsection

@section('page-description')
    Manage user accounts, permissions, and security settings
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Total Users</div>
                <div class="text-2xl font-semibold text-gray-900">{{ $stats['total_users'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Active Users</div>
                <div class="text-2xl font-semibold text-green-600">{{ $stats['active_users'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Vendors</div>
                <div class="text-2xl font-semibold text-yellow-600">{{ $stats['vendors'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Banned Users</div>
                <div class="text-2xl font-semibold text-red-600">{{ $stats['banned_users'] ?? 0 }}</div>
            </div>
        </div>

        {{-- Filters and Search --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    {{-- Search --}}
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text"
                               name="search"
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Username, email..."
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                                id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
                        </select>
                    </div>

                    {{-- Trust Level Filter --}}
                    <div>
                        <label for="trust_level" class="block text-sm font-medium text-gray-700 mb-1">Trust Level</label>
                        <select name="trust_level"
                                id="trust_level"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Levels</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ request('trust_level') == $i ? 'selected' : '' }}>Level {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Role Filter --}}
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role"
                                id="role"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Roles</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="moderator" {{ request('role') == 'moderator' ? 'selected' : '' }}>Moderator</option>
                            <option value="vendor" {{ request('role') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                            <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                        </select>
                    </div>

                    {{-- Date Range --}}
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Joined From</label>
                        <input type="date"
                               name="date_from"
                               id="date_from"
                               value="{{ request('date_from') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        Filter Users
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>

        {{-- Users Table --}}
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Users ({{ $users->total() }} total)
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trust Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Seen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-yellow-700 font-medium text-sm">{{ substr($user->username_pub, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $user->username_pub }}</div>
                                        <div class="text-sm text-gray-500">ID: {{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Level {{ $user->trust_level }}
                                    </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                @elseif($user->status === 'banned')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Banned
                                        </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->last_seen ? $user->last_seen->diffForHumans() : 'Never' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->orders_count ?? 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                        View
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="px-3 py-1 text-xs bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">
                                        Edit
                                    </a>
                                    @if(!$user->hasRole('vendor'))
                                        <form action="{{ route('admin.users.promote-to-vendor', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-purple-100 text-purple-700 rounded hover:bg-purple-200"
                                                    title="Promote to vendor without payment">
                                                Make Vendor
                                            </button>
                                        </form>
                                    @endif
                                    @if($user->status !== 'banned')
                                        <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">
                                                Ban
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">
                                                Unban
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No users found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
