@extends('layouts.admin')

@section('title', 'Manage Canaries')

@section('page-heading')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-amber-900">Manage Canaries</h1>
            <p class="text-amber-700 mt-1">Create and manage warrant canary messages</p>
        </div>
        <a href="{{ route('admin.canaries.create') }}" 
           class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
            Create New Canary
        </a>
    </div>
@endsection

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden border border-amber-200">
    <div class="px-6 py-4 bg-amber-50 border-b border-amber-200">
        <h2 class="text-xl font-bold text-amber-900">All Canaries</h2>
        <p class="text-sm text-amber-700 mt-1">
            {{ $canaries->total() }} {{ Str::plural('canary', $canaries->total()) }} total
        </p>
    </div>
    
    <div class="overflow-x-auto">
        @if($canaries->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message Preview</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($canaries as $canary)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                #{{ $canary->id }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-md truncate">
                                    {{ Str::limit($canary->message, 100) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $canary->created_at->format('M j, Y g:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                <a href="{{ route('admin.canaries.edit', $canary) }}" 
                                   class="text-amber-600 hover:text-amber-900">
                                    Edit
                                </a>
                                <form action="{{ route('admin.canaries.destroy', $canary) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this canary?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $canaries->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-gray-500">No canaries created yet.</p>
                <a href="{{ route('admin.canaries.create') }}" 
                   class="inline-block mt-4 text-amber-600 hover:text-amber-800">
                    Create your first canary →
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
