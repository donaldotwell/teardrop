@extends('layouts.vendor')

@section('page-title', $base->name . ' — FSAID Base')

@section('content')
<div class="max-w-full px-4 py-8">

    <div class="mb-6">
        <a href="{{ route('vendor.fsaid.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to FSAID</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $base->name }}</h1>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    {{-- Base stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-gray-900">{{ number_format($base->record_count) }}</div>
            <div class="text-xs text-gray-500 mt-1">Total Records</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-green-700">{{ number_format($base->available_count) }}</div>
            <div class="text-xs text-gray-500 mt-1">Available</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-amber-700">{{ number_format($base->sold_count) }}</div>
            <div class="text-xs text-gray-500 mt-1">Sold</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-purple-700">${{ number_format($base->price_usd, 2) }}</div>
            <div class="text-xs text-gray-500 mt-1">Per Record</div>
        </div>
    </div>

    {{-- Controls --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <form action="{{ route('vendor.fsaid.toggle', $base) }}" method="POST" class="inline">
            @csrf
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium rounded-lg border transition-colors
                           {{ $base->is_active
                               ? 'border-amber-300 text-amber-700 hover:bg-amber-50'
                               : 'border-green-300 text-green-700 hover:bg-green-50' }}">
                {{ $base->is_active ? 'Deactivate Base' : 'Activate Base' }}
            </button>
        </form>

        @if($base->sold_count === 0)
        <form action="{{ route('vendor.fsaid.destroy', $base) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                    onclick="return confirm('Permanently delete this base and all {{ $base->record_count }} records?')"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition-colors">
                Delete Base
            </button>
        </form>
        @endif

        <span class="px-3 py-2 text-xs rounded-lg {{ $base->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
            {{ $base->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    {{-- ── Filter panel ──────────────────────────────────────────────── --}}
    @php
        $advancedFilters = array_filter([
            request('state'), request('city'), request('level'),
            request('enrollment'), request('two_fa'), request('gender'), request('country'),
        ], fn($v) => $v !== null && $v !== '');
        $advancedCount = count($advancedFilters);
        $anyFilter = request()->anyFilled(['q','status','sort','state','city','level','enrollment','two_fa','gender','country']);
    @endphp

    <form method="GET" action="{{ route('vendor.fsaid.show', $base) }}" class="mb-5">

        {{-- Primary row: search + status pills + sort + actions --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="flex flex-wrap gap-3 items-end">

                {{-- Search --}}
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Name, email or FA username…"
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-purple-500">
                </div>

                {{-- Status pills --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <div class="flex rounded-lg border border-gray-300 overflow-hidden text-sm divide-x divide-gray-300">
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="" class="sr-only peer"
                                   {{ !request('status') ? 'checked' : '' }}>
                            <span class="block px-4 py-2 text-gray-600 hover:bg-gray-50
                                         peer-checked:bg-purple-700 peer-checked:text-white transition-colors">
                                All
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="available" class="sr-only peer"
                                   {{ request('status') === 'available' ? 'checked' : '' }}>
                            <span class="block px-4 py-2 text-gray-600 hover:bg-gray-50
                                         peer-checked:bg-green-600 peer-checked:text-white transition-colors">
                                Available
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="sold" class="sr-only peer"
                                   {{ request('status') === 'sold' ? 'checked' : '' }}>
                            <span class="block px-4 py-2 text-gray-600 hover:bg-gray-50
                                         peer-checked:bg-amber-500 peer-checked:text-white transition-colors">
                                Sold
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Sort --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sort</label>
                    <select name="sort"
                            class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-purple-500 bg-white">
                        <option value="default" {{ request('sort','default') === 'default' ? 'selected' : '' }}>Status (avail. first)</option>
                        <option value="newest"  {{ request('sort') === 'newest'  ? 'selected' : '' }}>Newest first</option>
                        <option value="oldest"  {{ request('sort') === 'oldest'  ? 'selected' : '' }}>Oldest first</option>
                        <option value="name"    {{ request('sort') === 'name'    ? 'selected' : '' }}>Name A–Z</option>
                        <option value="email"   {{ request('sort') === 'email'   ? 'selected' : '' }}>Email A–Z</option>
                    </select>
                </div>

                {{-- Action buttons --}}
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-sm font-semibold rounded-lg transition-colors">
                        Search
                    </button>
                    @if($anyFilter)
                    <a href="{{ route('vendor.fsaid.show', $base) }}"
                       class="px-4 py-2 border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm rounded-lg transition-colors">
                        Clear
                    </a>
                    @endif
                </div>
            </div>

            {{-- Advanced filters (collapsible) --}}
            <details class="mt-4" {{ $advancedCount > 0 ? 'open' : '' }}>
                <summary class="cursor-pointer text-sm text-purple-700 hover:text-purple-800 select-none inline-flex items-center gap-2">
                    <span>Advanced filters</span>
                    @if($advancedCount > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold bg-purple-700 text-white rounded-full">{{ $advancedCount }}</span>
                    @endif
                </summary>

                <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-4">

                    {{-- State --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                        <select name="state"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-purple-500 bg-white">
                            <option value="">Any</option>
                            @foreach($states as $st)
                                <option value="{{ $st }}" {{ request('state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- City --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                        <input type="text" name="city" value="{{ request('city') }}" placeholder="Any city"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-purple-500">
                    </div>

                    {{-- Level --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Level</label>
                        <select name="level"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-purple-500 bg-white">
                            <option value="">Any</option>
                            <option value="university"           {{ request('level') === 'university'           ? 'selected' : '' }}>University</option>
                            <option value="college"              {{ request('level') === 'college'              ? 'selected' : '' }}>College</option>
                            <option value="university withdrawn" {{ request('level') === 'university withdrawn' ? 'selected' : '' }}>University Withdrawn</option>
                            <option value="college withdrawn"    {{ request('level') === 'college withdrawn'    ? 'selected' : '' }}>College Withdrawn</option>
                        </select>
                    </div>

                    {{-- Enrollment --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Enrollment</label>
                        <select name="enrollment"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-purple-500 bg-white">
                            <option value="">Any</option>
                            <option value="enrolled"  {{ request('enrollment') === 'enrolled'  ? 'selected' : '' }}>Enrolled</option>
                            <option value="graduated" {{ request('enrollment') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                            <option value="withdrawn" {{ request('enrollment') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                        </select>
                    </div>

                    {{-- 2FA --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">2FA</label>
                        <select name="two_fa"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-purple-500 bg-white">
                            <option value="">Any</option>
                            <option value="yes" {{ request('two_fa') === 'yes' ? 'selected' : '' }}>Has 2FA</option>
                            <option value="no"  {{ request('two_fa') === 'no'  ? 'selected' : '' }}>No 2FA</option>
                        </select>
                    </div>

                    {{-- Gender --}}
                    @if($genders->isNotEmpty())
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Gender</label>
                        <select name="gender"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-purple-500 bg-white">
                            <option value="">Any</option>
                            @foreach($genders as $g)
                                <option value="{{ $g }}" {{ request('gender') === $g ? 'selected' : '' }}>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Country --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Country</label>
                        <input type="text" name="country" value="{{ request('country') }}" placeholder="Any country"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-purple-500">
                    </div>

                </div>
            </details>
        </div>

        {{-- Active filter summary --}}
        @if($anyFilter)
        <div class="mt-2 flex flex-wrap gap-2 text-xs">
            @if(request('q'))
                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full">Search: "{{ request('q') }}"</span>
            @endif
            @if(request('status'))
                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full">Status: {{ ucfirst(request('status')) }}</span>
            @endif
            @if(request('state'))
                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full">State: {{ request('state') }}</span>
            @endif
            @if(request('city'))
                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full">City: {{ request('city') }}</span>
            @endif
            @if(request('level'))
                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full">Level: {{ request('level') }}</span>
            @endif
            @if(request('enrollment'))
                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full">Enrollment: {{ request('enrollment') }}</span>
            @endif
            @if(request('two_fa'))
                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full">2FA: {{ request('two_fa') === 'yes' ? 'Has 2FA' : 'No 2FA' }}</span>
            @endif
            @if(request('gender'))
                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full">Gender: {{ request('gender') }}</span>
            @endif
            @if(request('country'))
                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full">Country: {{ request('country') }}</span>
            @endif
            <span class="px-2 py-1 text-gray-500">{{ number_format($records->total()) }} result{{ $records->total() !== 1 ? 's' : '' }}</span>
        </div>
        @endif

    </form>

    {{-- Records table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
        <table class="text-xs whitespace-nowrap">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Name</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Country</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Email</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">FA Uname</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Security Q&amp;A</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">State</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Gender</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">ZIP</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">DOB</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Address</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">SSN</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">CS</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">City</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Enrollment</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Level</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Programs</th>
                    <th class="px-3 py-2 text-center font-semibold text-gray-700">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($records as $record)
                <tr class="hover:bg-purple-50 transition-colors">
                    <td class="px-3 py-2 font-medium text-gray-900">{{ $record->first_name }} {{ $record->last_name }}</td>
                    <td class="px-3 py-2 text-gray-600">{{ $record->country ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-600 font-mono">{{ $record->email }}</td>
                    <td class="px-3 py-2 text-gray-600 font-mono">{{ $record->fa_uname ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-600 max-w-xs truncate" title="{{ $record->security_qa }}">{{ $record->security_qa ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-600">{{ $record->state ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-600">{{ $record->gender ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-600 font-mono">{{ $record->zip ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-600 font-mono">{{ $record->dob ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-600 max-w-xs truncate" title="{{ $record->address }}">{{ $record->address ?? '—' }}</td>
                    <td class="px-3 py-2 font-mono font-semibold text-red-700">{{ $record->ssn ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-600">{{ $record->cs ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-600">{{ $record->city ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-600">
                        @if($record->enrollment)
                            {{ ucfirst($record->enrollment) }}
                        @else
                            <span class="text-gray-400">N/A</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-gray-600">{{ $record->level ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-600 max-w-xs truncate" title="{{ $record->programs }}">{{ $record->programs ?? '—' }}</td>
                    <td class="px-3 py-2 text-center">
                        @if($record->status === 'available')
                            <span class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-xs">Available</span>
                        @else
                            <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-xs">Sold</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="17" class="px-4 py-10 text-center text-gray-500 text-sm">
                        No records match your filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">{{ $records->links() }}</div>
</div>
@endsection
