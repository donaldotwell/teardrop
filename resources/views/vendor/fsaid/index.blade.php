@extends('layouts.vendor')

@section('page-title', 'FSAID — My Bases')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">FSAID</h1>
            <p class="text-sm text-gray-500 mt-1">Upload CSV files and sell FSAID records to buyers.</p>
        </div>
        <a href="{{ route('vendor.fsaid.create') }}"
           class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-sm font-medium rounded-lg transition-colors">
            Upload New Base
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
    @endif

    @if($bases->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500 mb-4">You have not uploaded any FSAID bases yet.</p>
            <a href="{{ route('vendor.fsaid.create') }}"
               class="px-5 py-2 bg-purple-700 text-white text-sm font-medium rounded-lg hover:bg-purple-800 transition-colors">
                Upload First Base
            </a>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Base Name</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Price/Record</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Total</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Available</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Sold</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($bases as $base)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            <a href="{{ route('vendor.fsaid.show', $base) }}" class="hover:text-purple-700">
                                {{ $base->name }}
                            </a>
                            <div class="text-xs text-gray-400">{{ $base->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-3 text-right font-mono">${{ number_format($base->price_usd, 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($base->record_count) }}</td>
                        <td class="px-4 py-3 text-right text-green-700 font-medium">{{ number_format($base->available_count) }}</td>
                        <td class="px-4 py-3 text-right text-amber-700 font-medium">{{ number_format($base->sold_count) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($base->is_active)
                                <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('vendor.fsaid.show', $base) }}"
                                   class="text-xs text-purple-700 hover:underline">View</a>

                                <form action="{{ route('vendor.fsaid.toggle', $base) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-amber-700 hover:underline">
                                        {{ $base->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                @if($base->sold_count === 0)
                                <form action="{{ route('vendor.fsaid.destroy', $base) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:underline"
                                            onclick="return confirm('Delete this base and all records?')">
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4">{{ $bases->links() }}</div>
    @endif
</div>
@endsection
