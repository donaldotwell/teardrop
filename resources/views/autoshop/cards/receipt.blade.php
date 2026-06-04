@extends('layouts.autoshop')

@section('page-title', 'Purchase Receipt — Cards')
@section('page-heading', 'Purchase Receipt')

@section('breadcrumbs')
<a href="{{ route('autoshop.cards.my-purchases') }}" class="hover:text-gray-900">My Purchases</a>
<span class="text-gray-400 mx-1">/</span>
<span>Receipt</span>
@endsection

@section('content')

    {{-- Purchase summary --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <div class="text-xs text-gray-500 mb-0.5">Base</div>
                <div class="font-medium">{{ $purchase->base?->name ?? 'Multiple Bases' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500 mb-0.5">Vendor</div>
                <div class="font-medium">{{ $purchase->vendor->username_pub }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500 mb-0.5">Cards</div>
                <div class="font-bold text-lg text-gray-900">{{ $purchase->record_count }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500 mb-0.5">Total Paid</div>
                <div class="font-bold text-amber-700">
                    {{ number_format($purchase->total_crypto, $purchase->currency === 'btc' ? 8 : 12) }}
                    {{ strtoupper($purchase->currency) }}
                </div>
                <div class="text-xs text-gray-400">${{ number_format($purchase->total_usd, 2) }} USD</div>
            </div>
        </div>
        <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-400">
            Purchased {{ $purchase->created_at->format('M d, Y H:i') }}
        </div>
    </div>

    {{-- Full card details revealed --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-3 border-b border-teal-200 bg-teal-50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-teal-900">Purchased Cards — Full Details</h2>
            <a href="{{ route('autoshop.cards.download', $purchase) }}"
               class="px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition-colors">
                Download CSV
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">#</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Card Number</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Exp</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">CVV</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Cardholder</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Address</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">City</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">State</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">ZIP</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Country</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Email</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Phone</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($purchase->records as $i => $card)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-3 py-2 font-mono font-semibold text-red-700">{{ $card->card_number }}</td>
                        <td class="px-3 py-2 font-mono">{{ $card->exp_month }}/{{ $card->exp_year }}</td>
                        <td class="px-3 py-2 font-mono font-semibold text-red-700">{{ $card->cvv }}</td>
                        <td class="px-3 py-2 font-medium text-gray-900">{{ $card->cardholder_name }}</td>
                        <td class="px-3 py-2">{{ $card->address ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $card->city ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $card->state ?? '—' }}</td>
                        <td class="px-3 py-2 font-mono">{{ $card->zip ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $card->country ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $card->email ?? '—' }}</td>
                        <td class="px-3 py-2 font-mono">{{ $card->phone ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
