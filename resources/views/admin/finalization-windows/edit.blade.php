@extends('layouts.admin')

@section('title', 'Edit Finalization Window')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.finalization-windows.index') }}"
           class="text-gray-600 hover:text-gray-900">← Back</a>
        <h1 class="text-3xl font-bold text-gray-900">Edit: {{ $finalizationWindow->name }}</h1>
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

    <!-- Usage Warning -->
    @if($finalizationWindow->productCategories()->count() > 0 || $finalizationWindow->orders()->count() > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <h3 class="font-semibold text-amber-900 mb-2">⚠️ This Window is In Use</h3>
        <p class="text-sm text-amber-800">
            This finalization window is being used by {{ $finalizationWindow->productCategories()->count() }}
            product {{ $finalizationWindow->productCategories()->count() === 1 ? 'category' : 'categories' }}
            and has been used for {{ $finalizationWindow->orders()->count() }}
            {{ $finalizationWindow->orders()->count() === 1 ? 'order' : 'orders' }}.
            Changes will affect new orders only.
        </p>
    </div>
    @endif

    <form action="{{ route('admin.finalization-windows.update', $finalizationWindow) }}" method="POST" class="bg-white shadow-md rounded-lg p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Window Name *
            </label>
            <input type="text"
                   name="name"
                   id="name"
                   value="{{ old('name', $finalizationWindow->name) }}"
                   maxlength="100"
                   required
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">
            <p class="mt-1 text-sm text-gray-500">A descriptive name (e.g., "10 Minutes", "7 Days")</p>
        </div>

        <div>
            <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                Duration (Minutes) *
            </label>
            <input type="number"
                   name="duration_minutes"
                   id="duration_minutes"
                   value="{{ old('duration_minutes', $finalizationWindow->duration_minutes) }}"
                   min="0"
                   max="525600"
                   required
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">
            <p class="mt-1 text-sm text-gray-500">
                0 = Instant (no dispute window), Max = 525600 (1 year)
                <br>Common values: 10 min, 30 min, 60 min (1 hour), 10080 min (7 days), 30240 min (3 weeks)
            </p>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
            </label>
            <textarea name="description"
                      id="description"
                      rows="4"
                      maxlength="1000"
                      placeholder="Describe when this window should be used..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">{{ old('description', $finalizationWindow->description) }}</textarea>
        </div>

        <div>
            <label for="display_order" class="block text-sm font-medium text-gray-700 mb-2">
                Display Order *
            </label>
            <input type="number"
                   name="display_order"
                   id="display_order"
                   value="{{ old('display_order', $finalizationWindow->display_order) }}"
                   min="0"
                   required
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">
            <p class="mt-1 text-sm text-gray-500">Lower numbers appear first in lists</p>
        </div>

        <div class="flex items-start space-x-3">
            <input type="checkbox"
                   name="is_active"
                   id="is_active"
                   value="1"
                   {{ old('is_active', $finalizationWindow->is_active) ? 'checked' : '' }}
                   class="h-5 w-5 text-amber-600 focus:ring-amber-500 border-gray-300 rounded mt-0.5">
            <div>
                <label for="is_active" class="block text-sm font-medium text-gray-900">
                    Active
                </label>
                <p class="text-sm text-gray-500 mt-1">
                    Inactive windows cannot be assigned to new categories
                </p>
            </div>
        </div>

        <div class="flex space-x-4 pt-4 border-t border-gray-200">
            <button type="submit"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-md font-medium">
                Update Window
            </button>
            <a href="{{ route('admin.finalization-windows.index') }}"
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
