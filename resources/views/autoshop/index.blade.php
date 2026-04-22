@extends('layouts.app')

@section('page-title', 'Autoshop')
@section('page-heading', 'Autoshop')
@section('breadcrumbs', 'Autoshop')

@section('content')

    {{-- Sub-header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-5">
        <p class="text-sm text-gray-500">Browse and purchase records instantly. Payment deducted directly from your wallet.</p>
        <a href="{{ route('autoshop.my-purchases') }}"
           class="text-sm text-amber-700 hover:underline whitespace-nowrap sm:ml-4">My Purchases</a>
    </div>

    <div class="flex flex-col lg:flex-row gap-5">

        {{-- ── Filter sidebar ─────────────────────────────────────────── --}}
        <aside class="w-full lg:w-52 lg:flex-shrink-0">
            <form method="GET" action="{{ route('autoshop.index') }}">
                <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-5 lg:sticky lg:top-4">

                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Filters</span>
                        @if(request()->anyFilled(['base_id','vendor_id','state','gender','price_min','price_max','sort']))
                            <a href="{{ route('autoshop.index') }}"
                               class="text-xs text-amber-700 hover:underline">Clear</a>
                        @endif
                    </div>

                    {{-- Base --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Base</label>
                        <select name="base_id"
                                class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:border-amber-500 bg-white">
                            <option value="">All bases</option>
                            @foreach($activeBases as $base)
                                <option value="{{ $base->id }}" {{ request('base_id') == $base->id ? 'selected' : '' }}>
                                    {{ $base->name }}
                                    (${{ number_format($base->price_usd, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Vendor --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Vendor</label>
                        <select name="vendor_id"
                                class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:border-amber-500 bg-white">
                            <option value="">All vendors</option>
                            @foreach($activeBases->groupBy('vendor_id') as $vendorId => $vBases)
                                <option value="{{ $vendorId }}" {{ request('vendor_id') == $vendorId ? 'selected' : '' }}>
                                    {{ $vBases->first()->vendor->username_pub }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- State --}}
                    @if($states->isNotEmpty())
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                        <select name="state"
                                class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:border-amber-500 bg-white">
                            <option value="">Any state</option>
                            @foreach($states as $st)
                                <option value="{{ $st }}" {{ request('state') === $st ? 'selected' : '' }}>
                                    {{ $st }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Price range --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Price (USD / record)</label>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="price_min"
                                   value="{{ request('price_min') }}"
                                   placeholder="Min" min="0" step="0.01"
                                   class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:border-amber-500">
                            <span class="text-gray-400 text-xs flex-shrink-0">–</span>
                            <input type="number" name="price_max"
                                   value="{{ request('price_max') }}"
                                   placeholder="Max" min="0" step="0.01"
                                   class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:border-amber-500">
                        </div>
                    </div>

                    {{-- Sort --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sort by</label>
                        <select name="sort"
                                class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:border-amber-500 bg-white">
                            <option value="newest"     {{ $sort === 'newest'     ? 'selected' : '' }}>Newest first</option>
                            <option value="price_asc"  {{ $sort === 'price_asc'  ? 'selected' : '' }}>Price: low → high</option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: high → low</option>
                        </select>
                    </div>

                    <button type="submit"
                            class="w-full py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition-colors">
                        Apply Filters
                    </button>
                </div>
            </form>
        </aside>

        {{-- ── Records + purchase form ──────────────────────────────────── --}}
        <div class="flex-1 min-w-0">

            {{-- Result count --}}
            <div class="mb-3">
                <p class="text-sm text-gray-500">
                    {{ number_format($records->total()) }} record{{ $records->total() !== 1 ? 's' : '' }} available
                    @if($records->total() > 0) &mdash; ${{ number_format($records->min('price_usd'), 2) }}
                        @if($records->min('price_usd') != $records->max('price_usd'))
                            – ${{ number_format($records->max('price_usd'), 2) }}
                        @endif
                        / record
                    @endif
                </p>
            </div>

            @if($records->isEmpty())
                <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
                    <p class="text-gray-500 mb-3">No records match your filters.</p>
                    @if(request()->anyFilled(['base_id','vendor_id','state','gender','price_min','price_max','sort']))
                        <a href="{{ route('autoshop.index') }}" class="text-sm text-amber-700 hover:underline">Clear filters</a>
                    @endif
                </div>
            @else

            {{-- Purchase form wraps the table --}}
            <form action="{{ route('autoshop.purchase') }}" method="POST">
                @csrf

                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-3">

                    {{-- Info bar --}}
                    <div class="px-4 py-2.5 bg-amber-50 border-b border-amber-100 text-xs text-amber-800">
                        Select records you want to buy. SSN, DOB, and address are revealed after purchase.
                        All selected records must be from the <strong>same vendor</strong>.
                        Selections apply to this page — paginate to buy across pages.
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-3 py-2 w-8"></th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Name</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">City</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">State</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700 hidden sm:table-cell">ZIP</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700 hidden md:table-cell">Base</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700 hidden md:table-cell">Vendor</th>
                                    <th class="px-3 py-2 text-right font-semibold text-gray-700">Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($records as $record)
                                <tr class="hover:bg-amber-50">
                                    <td class="px-3 py-2 text-center">
                                        <input type="checkbox"
                                               name="fullz_ids[]"
                                               value="{{ $record->id }}"
                                               class="w-4 h-4 accent-amber-600">
                                    </td>
                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $record->name }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $record->city ?? '—' }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $record->state ?? '—' }}</td>
                                    <td class="px-3 py-2 text-gray-500 hidden sm:table-cell">{{ $record->zip ?? '—' }}</td>
                                    <td class="px-3 py-2 text-gray-500 text-xs hidden md:table-cell">
                                        <a href="{{ route('autoshop.index', ['base_id' => $record->base_id]) }}"
                                           class="hover:text-amber-700">{{ $record->base_name }}</a>
                                    </td>
                                    <td class="px-3 py-2 text-gray-500 text-xs hidden md:table-cell">
                                        <a href="{{ route('autoshop.index', ['vendor_id' => $record->base_vendor_id]) }}"
                                           class="hover:text-amber-700">
                                            {{ $activeBases->firstWhere('vendor_id', $record->base_vendor_id)?->vendor?->username_pub ?? '—' }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono text-amber-700 font-medium">
                                        ${{ number_format($record->price_usd, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Purchase bar --}}
                <div class="bg-white border border-gray-200 rounded-xl px-5 py-4">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="text-sm font-medium text-gray-700">Pay with:</label>
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                <input type="radio" name="currency" value="btc" checked class="accent-amber-600">
                                Bitcoin (BTC)
                            </label>
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                <input type="radio" name="currency" value="xmr" class="accent-orange-600">
                                Monero (XMR)
                            </label>
                        </div>
                        <button type="submit"
                                class="sm:ml-auto px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm rounded-lg transition-colors">
                            Purchase Selected
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        Total = sum of each selected record's price. No escrow — deducted directly from your wallet.
                    </p>
                </div>
            </form>

            {{-- Pagination --}}
            <div class="mt-3">{{ $records->links() }}</div>

            @endif
        </div>
    </div>

@endsection
