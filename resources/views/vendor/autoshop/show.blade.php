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
