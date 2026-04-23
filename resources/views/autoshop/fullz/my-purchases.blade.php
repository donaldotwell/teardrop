@extends('layouts.autoshop')

@section('page-title', 'My Purchases — Autoshop')
@section('page-heading', 'My Purchases')

@section('breadcrumbs')<span>My Purchases</span>@endsection

@section('content')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-5">
        <p class="text-sm text-gray-500">Click a purchase to view full record details.</p>
        <a href="{{ route('autoshop.fullz.index') }}" class="text-sm text-teal-700 hover:underline sm:ml-4">Browse Fullz</a>
    </div>

    @if($purchases->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500 mb-4">You have not purchased any records yet.</p>
            <a href="{{ route('autoshop.fullz.index') }}"
               class="px-5 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 transition-colors">
                Browse Fullz
            </a>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Date</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Base</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 hidden sm:table-cell">Vendor</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Qty</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Paid</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($purchases as $p)
                    <tr class="hover:bg-teal-50 transition-colors">
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $p->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $p->base?->name ?? 'Multiple Bases' }}</td>
                        <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">{{ $p->vendor->username_pub }}</td>
                        <td class="px-4 py-3 text-right font-medium">{{ $p->record_count }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="font-mono text-amber-700 text-xs">
                                {{ number_format($p->total_crypto, $p->currency === 'btc' ? 8 : 12) }}
                                <span class="uppercase text-amber-600">{{ $p->currency }}</span>
                            </div>
                            <div class="text-xs text-gray-400">${{ number_format($p->total_usd, 2) }}</div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('autoshop.fullz.receipt', $p) }}"
                               class="text-xs text-teal-700 hover:underline font-medium whitespace-nowrap">
                                View Records
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4">{{ $purchases->links() }}</div>
    @endif

@endsection
