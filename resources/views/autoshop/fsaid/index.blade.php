@extends('layouts.autoshop')

@section('page-title', 'FSAID — Autoshop')
@section('page-heading', 'Browse FSAID')
@section('breadcrumbs')<span>FSAID</span>@endsection

@section('content')

@php
    $hasFilters = request()->anyFilled(['vendor_id','base_id','state','city','zip','name','two_fa','level','enrollment','price_min','price_max']);
    $advancedActive = request()->anyFilled(['city','zip','two_fa','level','enrollment','price_min','price_max']);
@endphp

    {{-- ── Filter card ──────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('autoshop.fsaid.index') }}" class="mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">

            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Filters</span>
            </div>

            {{-- Row 1: Vendor · Base · State · Name --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Vendor</label>
                    <select name="vendor_id"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                        <option value="">All vendors</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Base</label>
                    <select name="base_id"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                        <option value="">All bases</option>
                        @foreach($activeBases as $b)
                            <option value="{{ $b->id }}" {{ request('base_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} (${{ number_format($b->price_usd, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                    <select name="state"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                        <option value="">Any state</option>
                        @foreach($states as $st)
                            <option value="{{ $st }}" {{ request('state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                    <input type="text" name="name" value="{{ request('name') }}"
                           placeholder="First or last name"
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500">
                </div>
            </div>

            {{-- More filters (collapsible, no JS — pure <details>) --}}
            <details {{ $advancedActive ? 'open' : '' }} class="group">
                <summary class="cursor-pointer text-xs font-medium text-teal-700 hover:text-teal-800 select-none list-none">
                    [+ More filters{{ $advancedActive ? ' (active)' : '' }}]
                </summary>

                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                        <input type="text" name="city" value="{{ request('city') }}"
                               placeholder="Any city"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ZIP</label>
                        <input type="text" name="zip" value="{{ request('zip') }}"
                               placeholder="Exact ZIP"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">2FA</label>
                        <select name="two_fa"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                            <option value="">Any</option>
                            <option value="yes" {{ request('two_fa') === 'yes' ? 'selected' : '' }}>Has 2FA</option>
                            <option value="no"  {{ request('two_fa') === 'no'  ? 'selected' : '' }}>No 2FA</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Level</label>
                        <select name="level"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                            <option value="">Any level</option>
                            <option value="university"           {{ request('level') === 'university'           ? 'selected' : '' }}>University</option>
                            <option value="college"              {{ request('level') === 'college'              ? 'selected' : '' }}>College</option>
                            <option value="university withdrawn" {{ request('level') === 'university withdrawn' ? 'selected' : '' }}>University Withdrawn</option>
                            <option value="college withdrawn"    {{ request('level') === 'college withdrawn'    ? 'selected' : '' }}>College Withdrawn</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Enrollment</label>
                        <select name="enrollment"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                            <option value="">Any</option>
                            <option value="enrolled"  {{ request('enrollment') === 'enrolled'  ? 'selected' : '' }}>Enrolled</option>
                            <option value="graduated" {{ request('enrollment') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                            <option value="withdrawn" {{ request('enrollment') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Price (USD / record)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="price_min" value="{{ request('price_min') }}"
                                   placeholder="Min" min="0" step="0.01"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500">
                            <span class="text-gray-400 flex-shrink-0">–</span>
                            <input type="number" name="price_max" value="{{ request('price_max') }}"
                                   placeholder="Max" min="0" step="0.01"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sort by</label>
                        <select name="sort"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                            <option value="newest"     {{ $sort === 'newest'     ? 'selected' : '' }}>Newest first</option>
                            <option value="price_asc"  {{ $sort === 'price_asc'  ? 'selected' : '' }}>Price: low → high</option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: high → low</option>
                        </select>
                    </div>
                </div>
            </details>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    Apply Filters
                </button>
                <a href="{{ route('autoshop.fsaid.index') }}"
                   class="px-5 py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                    Reset
                </a>
            </div>

        </div>
    </form>

    {{-- Result count --}}
    <p class="text-sm text-gray-500 mb-4">
        {{ number_format($records->total()) }} record{{ $records->total() !== 1 ? 's' : '' }} available
        @if($records->total() > 0)
            &mdash; ${{ number_format($records->min('price_usd'), 2) }}
            @if($records->min('price_usd') != $records->max('price_usd'))
                – ${{ number_format($records->max('price_usd'), 2) }}
            @endif
            / record
        @endif
    </p>

    {{-- Records + purchase form --}}
    @if($records->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500 mb-3">No records match your filters.</p>
            @if($hasFilters)
                <a href="{{ route('autoshop.fsaid.index') }}" class="text-sm text-teal-700 hover:underline">Clear filters</a>
            @endif
        </div>
    @else

    <form action="{{ route('autoshop.fsaid.purchase') }}" method="POST">
        @csrf

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4">
            <div class="px-4 py-3 bg-teal-50 border-b border-teal-100 text-xs text-teal-800">
                Select records to buy. Email credentials, SSN, DOB, and address are revealed after purchase.
                All selected records must be from the <strong>same vendor</strong>. Selections apply to this page only.
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-3 py-2 w-8"></th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Vendor</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Base</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Name</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">DOB</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">State</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">ZIP</th>
                            <th class="px-3 py-2 text-center font-semibold text-gray-700">Email</th>
                            <th class="px-3 py-2 text-center font-semibold text-gray-700">E.Pass</th>
                            <th class="px-3 py-2 text-center font-semibold text-gray-700">Backup</th>
                            <th class="px-3 py-2 text-center font-semibold text-gray-700">2FA</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Enrollment</th>
                            <th class="px-3 py-2 text-right font-semibold text-gray-700">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($records as $record)
                        <tr class="hover:bg-teal-50 transition-colors">
                            <td class="px-3 py-2 text-center">
                                <input type="checkbox" name="fsaid_ids[]" value="{{ $record->id }}"
                                       class="w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap">
                                <a href="{{ route('autoshop.fsaid.index', ['vendor_id' => $record->base_vendor_id]) }}"
                                   class="hover:text-teal-700">{{ $record->vendor_name }}</a>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap">
                                <a href="{{ route('autoshop.fsaid.index', ['vendor_id' => $record->base_vendor_id, 'base_id' => $record->base_id]) }}"
                                   class="hover:text-teal-700">{{ $record->base_name }}</a>
                            </td>
                            <td class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap">{{ $record->first_name }} {{ $record->last_name }}</td>
                            <td class="px-3 py-2 text-gray-600 font-mono text-xs whitespace-nowrap">{{ $record->dob ? explode(' ', trim($record->dob))[0] : '—' }}</td>
                            <td class="px-3 py-2 text-gray-600 text-xs">{{ $record->state ?? '—' }}</td>
                            <td class="px-3 py-2 text-gray-500 font-mono text-xs">{{ $record->zip ?? '—' }}</td>
                            <td class="px-3 py-2 text-center">
                                @if($record->email)
                                    <span class="text-green-600 font-bold">&#10003;</span>
                                @else
                                    <span class="text-red-400">&#10007;</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if($record->email_pass)
                                    <span class="text-green-600 font-bold">&#10003;</span>
                                @else
                                    <span class="text-red-400">&#10007;</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if($record->backup_code)
                                    <span class="text-green-600 font-bold">&#10003;</span>
                                @else
                                    <span class="text-red-400">&#10007;</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if($record->two_fa)
                                    <span class="text-green-600 font-bold">&#10003;</span>
                                @else
                                    <span class="text-red-400">&#10007;</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-xs">
                                @if($record->enrollment)
                                    <span class="text-gray-700">{{ ucfirst($record->enrollment) }}</span>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right font-mono text-amber-700 font-medium whitespace-nowrap text-xs">
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
                        class="sm:ml-auto w-full sm:w-auto px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm rounded-lg transition-colors shadow-sm">
                    Purchase Selected
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-3">
                Total = sum of each selected record's price. No escrow — deducted directly from your wallet.
            </p>
        </div>
    </form>

    <div class="mt-4">{{ $records->links() }}</div>

    @endif

@endsection
