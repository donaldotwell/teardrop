@extends('layouts.admin')

@section('title', 'Create Finalization Window')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.finalization-windows.index') }}"
           class="text-gray-600 hover:text-gray-900">← Back</a>
        <h1 class="text-3xl font-bold text-gray-900">Create Finalization Window</h1>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.finalization-windows.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6 space-y-6">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Window Name *</label>
            <input type="text"
                   name="name"
                   id="name"
                   value="{{ old('name') }}"
                   required
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">
            <p class="mt-1 text-sm text-gray-500">E.g., "10 Minutes", "7 Days", "Instant"</p>
        </div>

        <div>
            <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">Duration (in minutes) *</label>
            <input type="number"
                   name="duration_minutes"
                   id="duration_minutes"
                   value="{{ old('duration_minutes', 0) }}"
                   min="0"
                   max="525600"
                   required
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">
            <p class="mt-1 text-sm text-gray-500">0 = instant (no window), 60 = 1 hour, 1440 = 1 day, 10080 = 7 days</p>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description"
                      id="description"
                      rows="3"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">{{ old('description') }}</textarea>
            <p class="mt-1 text-sm text-gray-500">Internal notes about when to use this window</p>
        </div>

        <div>
            <label for="display_order" class="block text-sm font-medium text-gray-700 mb-2">Display Order *</label>
            <input type="number"
                   name="display_order"
                   id="display_order"
                   value="{{ old('display_order', 0) }}"
                   min="0"
                   required
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">
            <p class="mt-1 text-sm text-gray-500">Order in which this appears in lists (lower = first)</p>
        </div>

        <div class="flex items-center">
            <input type="checkbox"
                   name="is_active"
                   id="is_active"
                   value="1"
                   {{ old('is_active', true) ? 'checked' : '' }}
                   class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
        </div>

        <div class="flex space-x-4">
            <button type="submit"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-md font-medium">
                Create Window
            </button>
            <a href="{{ route('admin.finalization-windows.index') }}"
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
