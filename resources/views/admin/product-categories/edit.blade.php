@extends('layouts.admin')

@section('title', 'Edit Category - ' . $productCategory->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.product-categories.index') }}"
           class="text-gray-600 hover:text-gray-900">← Back</a>
        <h1 class="text-3xl font-bold text-gray-900">Configure: {{ $productCategory->name }}</h1>
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

    <!-- Category Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-semibold text-blue-900 mb-2">Category Information</h3>
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-blue-700 font-medium">Products</dt>
                <dd class="text-blue-900">{{ $productCategory->products->count() }}</dd>
            </div>
            <div>
                <dt class="text-blue-700 font-medium">Active Listings</dt>
                <dd class="text-blue-900">{{ $productCategory->listings_count }}</dd>
            </div>
            <div>
                <dt class="text-blue-700 font-medium">Direct Payment Listings</dt>
                <dd class="text-blue-900">{{ $directPaymentListings }}</dd>
            </div>
        </dl>
    </div>

    <form action="{{ route('admin.product-categories.update', $productCategory) }}" method="POST" class="bg-white shadow-md rounded-lg p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="border-b border-gray-200 pb-4">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Category Settings</h2>
        </div>

        <!-- Category Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Category Name *
            </label>
            <input type="text"
                   name="name"
                   id="name"
                   value="{{ old('name', $productCategory->name) }}"
                   maxlength="50"
                   required
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">
            @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="border-b border-gray-200 pb-4 pt-4">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Early Finalization Settings</h2>
            <p class="text-sm text-gray-600 mb-4">
                Configure whether vendors can use early finalization (direct payment) for listings in this category.
            </p>
        </div>

        <div class="flex items-start space-x-3">
            <input type="checkbox"
                   name="allows_early_finalization"
                   id="allows_early_finalization"
                   value="1"
                   {{ old('allows_early_finalization', $productCategory->allows_early_finalization) ? 'checked' : '' }}
                   class="h-5 w-5 text-amber-600 focus:ring-amber-500 border-gray-300 rounded mt-0.5">
            <div>
                <label for="allows_early_finalization" class="block text-sm font-medium text-gray-900">
                    Enable Early Finalization
                </label>
                <p class="text-sm text-gray-500 mt-1">
                    Allow qualified vendors to receive payment directly instead of through escrow
                </p>
            </div>
        </div>

        <div id="finalization-settings" class="space-y-6 {{ old('allows_early_finalization', $productCategory->allows_early_finalization) ? '' : 'opacity-50 pointer-events-none' }}">
            <div>
                <label for="finalization_window_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Dispute Window *
                </label>
                <select name="finalization_window_id"
                        id="finalization_window_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="">-- Select Window --</option>
                    @foreach($finalizationWindows as $window)
                        <option value="{{ $window->id }}"
                                {{ old('finalization_window_id', $productCategory->finalization_window_id) == $window->id ? 'selected' : '' }}>
                            {{ $window->name }} ({{ $window->getHumanReadableDuration() }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500">
                    Time buyers have to file disputes after purchase
                </p>
            </div>

            <div>
                <label for="min_vendor_level_for_early" class="block text-sm font-medium text-gray-700 mb-2">
                    Minimum Vendor Level Required *
                </label>
                <input type="number"
                       name="min_vendor_level_for_early"
                       id="min_vendor_level_for_early"
                       value="{{ old('min_vendor_level_for_early', $productCategory->min_vendor_level_for_early) }}"
                       min="1"
                       max="100"
                       required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">
                <p class="mt-1 text-sm text-gray-500">
                    Only vendors at or above this level can use early finalization (recommended: 8+)
                </p>
            </div>

            <div>
                <label for="early_finalization_notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Admin Notes
                </label>
                <textarea name="early_finalization_notes"
                          id="early_finalization_notes"
                          rows="3"
                          placeholder="Internal notes about early finalization policy for this category..."
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-amber-500 focus:border-amber-500">{{ old('early_finalization_notes', $productCategory->early_finalization_notes) }}</textarea>
            </div>
        </div>

        <div class="flex space-x-4 pt-4 border-t border-gray-200">
            <button type="submit"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-md font-medium">
                Save Settings
            </button>
            <a href="{{ route('admin.product-categories.index') }}"
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md font-medium">
                Cancel
            </a>
        </div>
    </form>

    <!-- Warning if enabling -->
    @if(!$productCategory->allows_early_finalization)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h3 class="font-semibold text-yellow-900 mb-2">⚠️ Before Enabling</h3>
        <ul class="list-disc list-inside text-sm text-yellow-800 space-y-1">
            <li>Ensure vendors in this category are trustworthy</li>
            <li>Select an appropriate dispute window duration</li>
            <li>Set a reasonable minimum vendor level</li>
            <li>Monitor dispute rates after enabling</li>
        </ul>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('allows_early_finalization');
    const settings = document.getElementById('finalization-settings');

    checkbox.addEventListener('change', function() {
        if (this.checked) {
            settings.classList.remove('opacity-50', 'pointer-events-none');
        } else {
            settings.classList.add('opacity-50', 'pointer-events-none');
        }
    });
});
</script>
@endsection
