@extends('layouts.autoshop')

@section('page-title', 'Fullz — Autoshop')
@section('page-heading', 'Browse Fullz')
@section('breadcrumbs')<span>Fullz</span>@endsection

@section('content')

    {{-- Sub-header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-5">
        <p class="text-sm text-gray-500">Browse and purchase records instantly. Payment deducted directly from your wallet.</p>
    </div>

    {{-- ── Filter bar (top) ─────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('autoshop.fullz.index') }}" class="mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Filters</span>
                @if(request()->anyFilled(['base_id','vendor_id','state','gender','price_min','price_max','sort']))
                    <a href="{{ route('autoshop.fullz.index') }}" class="text-xs text-teal-700 hover:underline">Clear all</a>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                {{-- Base --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Base</label>
                    <select name="base_id"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                        <option value="">All bases</option>
                        @foreach($activeBases as $base)
                            <option value="{{ $base->id }}" {{ request('base_id') == $base->id ? 'selected' : '' }}>
                                {{ $base->name }} (${{ number_format($base->price_usd, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Vendor --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Vendor</label>
                    <select name="vendor_id"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                        <option value="">All vendors</option>
                        @foreach($activeBases->groupBy('vendor_id') as $vendorId => $vBases)
                            <option value="{{ $vendorId }}" {{ request('vendor_id') == $vendorId ? 'selected' : '' }}>
                                {{ $vBases->first()->vendor->username_pub }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- State --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                    <select name="state"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                        <option value="">Any state</option>
                        @foreach($states as $st)
                            <option value="{{ $st }}" {{ request('state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Price range --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Price (USD / record)</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="price_min" value="{{ request('price_min') }}"
                               placeholder="Min" min="0" step="0.01"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        <span class="text-gray-400 text-sm flex-shrink-0">–</span>
                        <input type="number" name="price_max" value="{{ request('price_max') }}"
                               placeholder="Max" min="0" step="0.01"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                    </div>
                </div>

                {{-- Sort --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sort by</label>
                    <select name="sort"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                        <option value="newest"     {{ $sort === 'newest'     ? 'selected' : '' }}>Newest first</option>
                        <option value="price_asc"  {{ $sort === 'price_asc'  ? 'selected' : '' }}>Price: low → high</option>
                        <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: high → low</option>
                    </select>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    Apply Filters
                </button>
                <a href="{{ route('autoshop.fullz.index') }}"
                   class="px-5 py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- ── Records + purchase form ──────────────────────────────────── --}}
    <div class="min-w-0">

        {{-- Result count --}}
        <div class="mb-4">
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
                    <a href="{{ route('autoshop.fullz.index') }}" class="text-sm text-teal-700 hover:underline">Clear filters</a>
                @endif
            </div>
        @else

        {{-- Purchase form wraps the table --}}
        <form action="{{ route('autoshop.fullz.purchase') }}" method="POST">
            @csrf

            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4">
                {{-- Info bar --}}
                <div class="px-4 py-3 bg-teal-50 border-b border-teal-100 text-xs text-teal-800">
                    <span class="block sm:inline">Select records you want to buy.</span>
                    <span class="block sm:inline sm:ml-1">SSN, DOB, and address are revealed after purchase.</span>
                    <span class="block sm:inline sm:ml-1">All selected records must be from the <strong>same vendor</strong>.</span>
                    <span class="block sm:inline sm:ml-1">Selections apply to this page — paginate to buy across pages.</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-3 py-3 w-8"></th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700">Name</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700 hidden sm:table-cell">City</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700">State</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700 hidden md:table-cell">ZIP</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700 hidden lg:table-cell">Base</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700 hidden xl:table-cell">Vendor</th>
                                <th class="px-3 py-3 text-right font-semibold text-gray-700">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($records as $record)
                            <tr class="hover:bg-teal-50 transition-colors">
                                <td class="px-3 py-3 text-center">
                                    <input type="checkbox" name="fullz_ids[]" value="{{ $record->id }}"
                                           class="w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                </td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $record->name }}</td>
                                <td class="px-3 py-3 text-gray-600 hidden sm:table-cell">{{ $record->city ?? '—' }}</td>
                                <td class="px-3 py-3 text-gray-600">{{ $record->state ?? '—' }}</td>
                                <td class="px-3 py-3 text-gray-500 hidden md:table-cell">{{ $record->zip ?? '—' }}</td>
                                <td class="px-3 py-3 text-gray-500 text-xs hidden lg:table-cell">
                                    <a href="{{ route('autoshop.fullz.index', ['base_id' => $record->base_id]) }}"
                                       class="hover:text-teal-700">{{ $record->base_name }}</a>
                                </td>
                                <td class="px-3 py-3 text-gray-500 text-xs hidden xl:table-cell">
                                    <a href="{{ route('autoshop.fullz.index', ['vendor_id' => $record->base_vendor_id]) }}"
                                       class="hover:text-teal-700">
                                        {{ $activeBases->firstWhere('vendor_id', $record->base_vendor_id)?->vendor?->username_pub ?? '—' }}
                                    </a>
                                </td>
                                <td class="px-3 py-3 text-right font-mono text-amber-700 font-medium whitespace-nowrap">
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
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="text-sm font-medium text-gray-700">Pay with:</label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="radio" name="currency" value="btc" checked class="w-4 h-4 text-amber-600 focus:ring-amber-500">
                            <span>Bitcoin (BTC)</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="radio" name="currency" value="xmr" class="w-4 h-4 text-orange-600 focus:ring-orange-500">
                            <span>Monero (XMR)</span>
                        </label>
                    </div>
                    <button type="submit"
                            class="sm:ml-auto w-full sm:w-auto px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-sm rounded-lg transition-colors shadow-sm">
                        Purchase Selected
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-3">
                    Total = sum of each selected record's price. No escrow — deducted directly from your wallet.
                </p>
            </div>
        </form>

        {{-- Pagination --}}
        <div class="mt-4">{{ $records->links() }}</div>

        @endif
    </div>

@endsection
