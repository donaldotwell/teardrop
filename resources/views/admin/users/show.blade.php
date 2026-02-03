@extends('layouts.admin')
@section('page-title', 'User Details')

@section('breadcrumbs')
    <a href="{{ route('admin.users.index') }}" class="text-yellow-700 hover:text-yellow-800">Users</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">{{ $user->username_pub }}</span>
@endsection

@section('page-heading')
    User Details: {{ $user->username_pub }}
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- User Overview --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                        <span class="text-yellow-700 font-bold text-xl">{{ substr($user->username_pub, 0, 1) }}</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $user->username_pub }}</h2>
                        <p class="text-gray-600">User ID: {{ $user->id }}</p>
                        <div class="flex items-center space-x-2 mt-2">
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
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Trust Level {{ $user->trust_level }}
                            </span>
                            @if($user->vendor_level > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Vendor Level {{ $user->vendor_level }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('admin.users.edit', $user) }}"
                       class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        Edit User
                    </a>
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

            {{-- User Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-6 border-t border-gray-100">
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900">{{ $user->orders->count() }}</div>
                    <div class="text-sm text-gray-600">Total Orders</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-green-600">
                        {{ $user->orders->where('status', 'completed')->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Completed Orders</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-yellow-600">
                        ${{ number_format($user->orders->where('status', 'completed')->sum('usd_price'), 2) }}
                    </div>
                    <div class="text-sm text-gray-600">Total Spent</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-amber-600">
                        {{ $user->wallets->sum('balance') }}
                    </div>
                    <div class="text-sm text-gray-600">Total Balance</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Account Information --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Private Username:</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $user->username_pri }}</dd>
                    </div>
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
                    @if($user->hasRole('vendor'))
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Early Finalization:</dt>
                            <dd class="text-sm font-medium">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ $user->early_finalization_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->early_finalization_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Early Finalization Stats:</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ $user->successful_early_finalized_orders }}/{{ $user->total_early_finalized_orders }} successful
                            </dd>
                        </div>
                    @endif
                </dl>

                {{-- Early Finalization Toggle for Vendors --}}
                @if($user->hasRole('vendor'))
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <form action="{{ route('admin.users.toggle-early-finalization', $user) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2 rounded {{ $user->early_finalization_enabled ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                {{ $user->early_finalization_enabled ? 'Disable Early Finalization' : 'Enable Early Finalization' }}
                            </button>
                        </form>
                        @if($user->total_early_finalized_orders > 0)
                            <div class="mt-2 text-xs text-gray-500 text-center">
                                Success Rate: {{ $user->total_early_finalized_orders > 0 ? round(($user->successful_early_finalized_orders / $user->total_early_finalized_orders) * 100, 1) : 0 }}%
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Wallet Information --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Wallet Balances</h3>

                <div class="space-y-4">
                    @foreach($user->wallets as $wallet)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                    {{ $wallet->currency === 'btc' ? 'bg-orange-100' : 'bg-purple-100' }}">
                                    <span class="font-bold text-sm
                                        {{ $wallet->currency === 'btc' ? 'text-orange-600' : 'text-amber-600' }}">
                                        {{ strtoupper($wallet->currency) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">
                                        {{ ucfirst($wallet->currency === 'btc' ? 'Bitcoin' : 'Monero') }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ strtoupper($wallet->currency) }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-medium text-gray-900">
                                    {{ number_format($wallet->balance, 8) }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    ≈ ${{ number_format($wallet->balance * ($wallet->currency === 'btc' ? 45000 : 150), 2) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                <a href="{{ route('admin.orders.index', ['search' => $user->username_pub]) }}"
                   class="text-sm text-yellow-600 hover:text-yellow-800">
                    View All Orders →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Listing</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @forelse($user->orders->take(5) as $order)
                        <tr>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                #{{ substr($order->uuid, 0, 8) }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                {{ $order->listing->title ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                ${{ number_format($order->usd_price, 2) }}
                            </td>
                            <td class="px-4 py-2">
                                @if($order->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                @elseif($order->status === 'completed')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Cancelled
                                        </span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                No orders found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- User Roles --}}
        @if($user->roles->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">User Roles</h3>

                <div class="flex flex-wrap gap-2">
                    @foreach($user->roles as $role)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            {{ ucfirst($role->name) }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
