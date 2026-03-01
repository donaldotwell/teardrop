@extends('layouts.admin')

@section('page-title', 'XMR Wallet: ' . $xmrWallet->name)
@section('page-heading', 'XMR Wallet: ' . $xmrWallet->name)
@section('page-description')
    @if($xmrWallet->user)
        Owner: {{ $xmrWallet->user->username_pub }}
    @else
        Escrow wallet (no user)
    @endif
@endsection

@section('breadcrumbs')
    <a href="{{ route('admin.wallets.xmr.index') }}" class="text-yellow-700 hover:text-yellow-800">XMR Wallets</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium">{{ $xmrWallet->name }}</span>
@endsection

@section('content')

    {{-- Wallet Info --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        {{-- Balance Card --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Balance</div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($xmrWallet->balance, 8) }} XMR</div>
            <div class="text-sm text-gray-600 mt-1">
                Unlocked: <span class="font-mono {{ $xmrWallet->unlocked_balance > 0 ? 'text-green-700' : 'text-gray-500' }}">{{ number_format($xmrWallet->unlocked_balance, 8) }}</span>
            </div>
            <div class="text-sm text-gray-500 mt-1">~ ${{ number_format(convert_crypto_to_usd($xmrWallet->balance, 'xmr'), 2) }} USD</div>
        </div>

        {{-- Stats Card --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Stats</div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Received</span>
                    <span class="font-mono text-gray-900">{{ number_format($xmrWallet->total_received, 8) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Sent</span>
                    <span class="font-mono text-gray-900">{{ number_format($xmrWallet->total_sent, 8) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Block Height</span>
                    <span class="font-mono text-gray-900">{{ number_format($xmrWallet->height) }}</span>
                </div>
            </div>
        </div>

        {{-- Details Card --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Details</div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Wallet ID</span>
                    <span class="text-gray-900">{{ $xmrWallet->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status</span>
                    @if($xmrWallet->is_active)
                        <span class="text-green-700 font-medium">Active</span>
                    @else
                        <span class="text-red-700 font-medium">Inactive</span>
                    @endif
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Last Synced</span>
                    <span class="text-gray-900">{{ $xmrWallet->last_synced_at ? $xmrWallet->last_synced_at->format('Y-m-d H:i') : 'Never' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Created</span>
                    <span class="text-gray-900">{{ $xmrWallet->created_at->format('Y-m-d H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Primary Address --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
        <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Primary Address</div>
        <div class="font-mono text-xs text-gray-900 break-all select-all bg-gray-50 p-2 rounded">{{ $xmrWallet->primary_address }}</div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3 mb-6">
        @if($xmrWallet->unlocked_balance > 0)
            <a href="{{ route('admin.wallets.xmr.transfer', $xmrWallet) }}"
               class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm font-medium">
                Transfer XMR
            </a>
        @endif
        <a href="{{ route('admin.wallets.xmr.show', [$xmrWallet, 'refresh' => 1]) }}"
           class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm font-medium">
            Refresh RPC Balance
        </a>
    </div>

    {{-- Addresses --}}
    @if($xmrWallet->addresses->count() > 0)
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Addresses ({{ $xmrWallet->addresses->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($xmrWallet->addresses->sortByDesc('address_index')->take(10) as $address)
                    <div class="px-4 py-3">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="font-mono text-xs text-gray-900 break-all">{{ $address->address }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    Index: {{ $address->account_index }}/{{ $address->address_index }}
                                    @if($address->is_used)
                                        <span class="ml-2 text-amber-600">[used]</span>
                                    @else
                                        <span class="ml-2 text-green-600">[current]</span>
                                    @endif
                                    @if($address->tx_count > 0)
                                        <span class="ml-2">{{ $address->tx_count }} txs</span>
                                    @endif
                                    @if($address->label)
                                        <span class="ml-2 text-gray-400">{{ $address->label }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right text-xs font-mono text-gray-600 whitespace-nowrap">
                                {{ number_format($address->balance, 8) }} XMR
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Transactions --}}
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Transactions</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-2 font-semibold text-gray-700">TXID</th>
                    <th class="text-left px-4 py-2 font-semibold text-gray-700">Type</th>
                    <th class="text-right px-4 py-2 font-semibold text-gray-700">Amount</th>
                    <th class="text-right px-4 py-2 font-semibold text-gray-700">Fee</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-700">Confs</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-700">Status</th>
                    <th class="text-right px-4 py-2 font-semibold text-gray-700">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $tx)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <div class="font-mono text-xs text-gray-900 truncate max-w-[200px]" title="{{ $tx->txid }}">
                                {{ $tx->txid ? Str::limit($tx->txid, 20) : '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-2">
                            @if($tx->type === 'deposit')
                                <span class="text-green-700 font-medium">Deposit</span>
                            @else
                                <span class="text-red-700 font-medium">Withdrawal</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right font-mono {{ $tx->type === 'deposit' ? 'text-green-700' : 'text-red-700' }}">
                            {{ $tx->type === 'deposit' ? '+' : '-' }}{{ number_format(abs($tx->amount), 8) }}
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-gray-500 text-xs">{{ $tx->fee ? number_format($tx->fee, 8) : '—' }}</td>
                        <td class="px-4 py-2 text-center text-gray-700">{{ $tx->confirmations ?? 0 }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($tx->status === 'unlocked')
                                <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-green-100 text-green-700">Unlocked</span>
                            @elseif($tx->status === 'confirmed')
                                <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-blue-100 text-blue-700">Confirmed</span>
                            @elseif($tx->status === 'pending')
                                <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-yellow-100 text-yellow-700">Pending</span>
                            @else
                                <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-gray-100 text-gray-700">{{ ucfirst($tx->status) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right text-xs text-gray-600">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    @endif

@endsection
