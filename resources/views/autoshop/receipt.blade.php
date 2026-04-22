@extends('layouts.app')

@section('page-title', 'Purchase Receipt — Autoshop')
@section('page-heading', 'Purchase Receipt')

@section('breadcrumbs')
<a href="{{ route('autoshop.index') }}" class="hover:text-gray-900">Autoshop</a>
<span class="text-gray-400 mx-1">/</span>
<a href="{{ route('autoshop.my-purchases') }}" class="hover:text-gray-900">My Purchases</a>
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
                <div class="text-xs text-gray-500 mb-0.5">Records</div>
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

    {{-- Full records revealed --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-3 border-b border-amber-200 bg-amber-50">
            <h2 class="text-sm font-semibold text-amber-900">Purchased Records — Full Details</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">#</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Name</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Address</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">City</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">State</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">ZIP</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Phone</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Gender</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">SSN</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">DOB</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($purchase->records as $i => $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-3 py-2 font-medium text-gray-900">{{ $r->name }}</td>
                        <td class="px-3 py-2">{{ $r->address ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->city ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->state ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->zip ?? '—' }}</td>
                        <td class="px-3 py-2 font-mono">{{ $r->phone_no ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->gender ?? '—' }}</td>
                        <td class="px-3 py-2 font-mono font-semibold text-red-700">{{ $r->ssn }}</td>
                        <td class="px-3 py-2 font-mono">{{ $r->dob }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
