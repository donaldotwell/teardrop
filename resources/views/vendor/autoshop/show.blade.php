@extends('layouts.vendor')

@section('page-title', 'Base: ' . $base->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="mb-5">
        <a href="{{ route('vendor.autoshop.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Autoshop</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    {{-- Base summary --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $base->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">Uploaded {{ $base->created_at->format('M d, Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($base->is_active)
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
                @else
                    <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Inactive</span>
                @endif
                <form action="{{ route('vendor.autoshop.toggle', $base) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1.5 border border-gray-300 text-gray-700 text-xs rounded-lg hover:bg-gray-50 transition-colors">
                        {{ $base->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 pt-5 border-t border-gray-100">
            <div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($base->record_count) }}</div>
                <div class="text-xs text-gray-500">Total Records</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-green-700">{{ number_format($base->available_count) }}</div>
                <div class="text-xs text-gray-500">Available</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-amber-700">{{ number_format($base->sold_count) }}</div>
                <div class="text-xs text-gray-500">Sold</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900">${{ number_format($base->price_usd, 2) }}</div>
                <div class="text-xs text-gray-500">Price / Record</div>
            </div>
        </div>
    </div>

    {{-- Management panel: edit + upload --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        {{-- Edit name / price --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Edit Base</h2>
            <form action="{{ route('vendor.autoshop.update', $base) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Base Name</label>
                    <input type="text" name="name" value="{{ old('name', $base->name) }}"
                           maxlength="120" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-purple-500 @error('name') border-red-400 @enderror">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Price per Record (USD)</label>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 text-sm">$</span>
                        <input type="number" name="price_usd" value="{{ old('price_usd', $base->price_usd) }}"
                               min="0.01" max="9999" step="0.01" required
                               class="w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-purple-500 @error('price_usd') border-red-400 @enderror">
                        <span class="text-xs text-gray-400">USD per record</span>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" name="update_existing" value="1"
                               class="mt-0.5 w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-xs text-gray-600">
                            Also reprice existing unsold records in this base
                            <span class="block text-gray-400 mt-0.5">If unchecked, only new uploads will use the new price.</span>
                        </span>
                    </label>
                </div>
                <button type="submit"
                        class="w-full py-2 bg-purple-700 hover:bg-purple-800 text-white text-sm font-semibold rounded-lg transition-colors">
                    Save Changes
                </button>
            </form>
        </div>

        {{-- Upload more records --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Add More Records</h2>
            <form action="{{ route('vendor.autoshop.upload', $base) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1">CSV File</label>
                    <input type="file" name="file" accept=".csv,.txt" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-purple-500 @error('file') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-1">Same format as original upload. Max 10 MB.</p>
                </div>
                <p class="text-xs text-gray-500 mb-4">Records will be appended to this base at the current price (${{ number_format($base->price_usd, 2) }}/record).</p>
                <button type="submit"
                        class="w-full py-2 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold rounded-lg transition-colors">
                    Upload and Append
                </button>
            </form>
        </div>

    </div>

    {{-- Records table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-800">Records</h2>
            <span class="text-xs text-gray-400">All fields visible to you as vendor</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Name</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Address</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">City</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">State</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">ZIP</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Phone</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Gender</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">SSN</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">DOB</th>
                        <th class="px-3 py-2 text-center font-semibold text-gray-600">Status</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Sold</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($records as $r)
                    <tr class="{{ $r->status === 'sold' ? 'bg-gray-50 text-gray-400' : '' }}">
                        <td class="px-3 py-2 font-medium">{{ $r->name }}</td>
                        <td class="px-3 py-2">{{ $r->address ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->city ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->state ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->zip ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->phone_no ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->gender ?? '—' }}</td>
                        <td class="px-3 py-2 font-mono">{{ $r->ssn }}</td>
                        <td class="px-3 py-2 font-mono">{{ $r->dob }}</td>
                        <td class="px-3 py-2 text-center">
                            @if($r->status === 'available')
                                <span class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-xs">Available</span>
                            @else
                                <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-xs">Sold</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-400">{{ $r->sold_at?->format('M d') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $records->links() }}</div>
</div>
@endsection
