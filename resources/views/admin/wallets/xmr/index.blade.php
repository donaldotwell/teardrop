@extends('layouts.admin')

@section('page-title', 'XMR Wallets')
@section('page-heading', 'Monero Wallets')
@section('page-description', 'View and manage all Monero wallets in the system')

@section('breadcrumbs')
    <span class="text-gray-900 font-medium">XMR Wallets</span>
@endsection

@section('content')

    {{-- RPC Status --}}
    @unless($rpcAvailable)
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded text-red-800">
            <div class="font-medium">Monero RPC Unavailable</div>
            <div class="text-sm">The monero-wallet-rpc daemon is not responding. Live balance queries and transfers will fail.</div>
        </div>
    @endunless

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Balance</div>
            <div class="text-lg font-bold text-gray-900">{{ number_format($totalBalance, 8) }} XMR</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Unlocked</div>
            <div class="text-lg font-bold text-gray-900">{{ number_format($totalUnlocked, 8) }} XMR</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Wallets</div>
            <div class="text-lg font-bold text-gray-900">{{ $totalWallets }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">User Wallets</div>
            <div class="text-lg font-bold text-gray-900">{{ $userWallets }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Escrow Wallets</div>
            <div class="text-lg font-bold text-gray-900">{{ $escrowWallets }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
        <form action="{{ route('admin.wallets.xmr.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Wallet name or username..."
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-amber-500 text-sm">
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" id="type" class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-amber-500 text-sm">
                    <option value="">All</option>
                    <option value="user" {{ request('type') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="escrow" {{ request('type') === 'escrow' ? 'selected' : '' }}>Escrow</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm font-medium">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'type']))
                    <a href="{{ route('admin.wallets.xmr.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm font-medium">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Wallets Table --}}
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-700">Wallet</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-700">Owner</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-700">Balance</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-700">Unlocked</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-700">Height</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-700">Last Sync</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($wallets as $wallet)
                    <tr class="hover:bg-amber-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $wallet->name }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $wallet->id }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($wallet->user)
                                <a href="{{ route('admin.users.show', $wallet->user) }}" class="text-amber-700 hover:text-amber-800 font-medium">
                                    {{ $wallet->user->username_pub }}
                                </a>
                                @if($wallet->user->hasRole('vendor'))
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">vendor</span>
                                @endif
                            @else
                                <span class="text-gray-400 italic">Escrow</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-900">{{ number_format($wallet->balance, 8) }}</td>
                        <td class="px-4 py-3 text-right font-mono {{ $wallet->unlocked_balance > 0 ? 'text-green-700' : 'text-gray-500' }}">
                            {{ number_format($wallet->unlocked_balance, 8) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-500 text-xs">{{ number_format($wallet->height) }}</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-600">
                            {{ $wallet->last_synced_at ? $wallet->last_synced_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.wallets.xmr.show', $wallet) }}" class="text-amber-700 hover:text-amber-800 text-sm font-medium">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No wallets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($wallets->hasPages())
        <div class="mt-4">
            {{ $wallets->links() }}
        </div>
    @endif

@endsection
