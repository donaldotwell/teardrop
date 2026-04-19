@extends('layouts.admin')

@section('page-title', 'Autoshop — Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Autoshop Overview</h1>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
        <div class="bg-white border border-gray-200 rounded-xl p-4 col-span-1">
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_bases']) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total Bases</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-2xl font-bold text-green-700">{{ number_format($stats['active_bases']) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Active Bases</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_records']) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total Records</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-2xl font-bold text-green-700">{{ number_format($stats['available']) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Available</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-2xl font-bold text-amber-700">{{ number_format($stats['sold']) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Sold</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_purchases']) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Purchases</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-2xl font-bold text-gray-900">${{ number_format($stats['revenue_usd'], 2) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Revenue (USD)</div>
        </div>
    </div>

    {{-- Bases table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-8">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-800">All Bases</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Base</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Vendor</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-600">Price</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-600">Total</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-600">Available</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-600">Sold</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-600">Purchases</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Created</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($bases as $base)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $base->name }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ $base->vendor->username_pub }}</td>
                        <td class="px-4 py-2 text-right font-mono">${{ number_format($base->price_usd, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($base->record_count) }}</td>
                        <td class="px-4 py-2 text-right text-green-700">{{ number_format($base->available_count) }}</td>
                        <td class="px-4 py-2 text-right text-amber-700">{{ number_format($base->sold_count) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($base->purchases_count) }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($base->is_active)
                                <span class="px-1.5 py-0.5 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
                            @else
                                <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-gray-400 text-xs">{{ $base->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-2">
                            <form action="{{ route('admin.autoshop.toggle-base', $base) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="text-xs {{ $base->is_active ? 'text-red-600' : 'text-green-700' }} hover:underline">
                                    {{ $base->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $bases->links() }}</div>
    </div>

    {{-- Recent purchases --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-800">Recent Purchases (last 50)</h2>
        </div>
        @if($recentPurchases->isEmpty())
            <div class="p-8 text-center text-sm text-gray-500">No purchases yet.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600">Date</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600">Buyer</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600">Vendor</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600">Base</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-600">Records</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-600">USD</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-600">Crypto</th>
                            <th class="px-4 py-2 text-center font-semibold text-gray-600">Currency</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentPurchases as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-500 text-xs">{{ $p->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-2">{{ $p->buyer->username_pub }}</td>
                            <td class="px-4 py-2">{{ $p->vendor->username_pub }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $p->base->name }}</td>
                            <td class="px-4 py-2 text-right font-medium">{{ $p->record_count }}</td>
                            <td class="px-4 py-2 text-right font-mono">${{ number_format($p->total_usd, 2) }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs">
                                {{ number_format($p->total_crypto, $p->currency === 'btc' ? 8 : 12) }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-xs rounded-full uppercase">
                                    {{ $p->currency }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
