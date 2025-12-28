@extends('layouts.vendor')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">Edit Listing</h1>
            <p class="text-sm text-gray-600 mt-1">Update your listing details</p>
        </div>

        <!-- Form -->
        <form action="{{ route('vendor.listings.update', $listing) }}" method="post" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-600">*</span></label>
                <input type="text" name="title" id="title" maxlength="140" required
                       value="{{ old('title', $listing->title) }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Short Description -->
            <div>
                <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">Short Description <span class="text-red-600">*</span></label>
                <input type="text" name="short_description" id="short_description" maxlength="255" required
                       value="{{ old('short_description', $listing->short_description) }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('short_description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Full Description <span class="text-red-600">*</span></label>
                <textarea name="description" id="description" rows="6" required
                          class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('description', $listing->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Current Category & Product (Read-only) -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Current Category & Product (Cannot be changed)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Category</div>
                        <div class="text-sm font-medium text-gray-900">{{ $listing->product->productCategory->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Product</div>
                        <div class="text-sm font-medium text-gray-900">{{ $listing->product->name }}</div>
                    </div>
                </div>
            </div>

            <!-- Price and Shipping -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price (USD) <span class="text-red-600">*</span></label>
                    <input type="number" name="price" id="price" step="0.01" min="0" required
                           value="{{ old('price', $listing->price) }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price_shipping" class="block text-sm font-medium text-gray-700 mb-2">Shipping Cost (USD) <span class="text-red-600">*</span></label>
                    <input type="number" name="price_shipping" id="price_shipping" step="0.01" min="0" required
                           value="{{ old('price_shipping', $listing->price_shipping) }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @error('price_shipping')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity <span class="text-red-600">*</span></label>
                    <input type="number" name="quantity" id="quantity" min="1" required
                           value="{{ old('quantity', $listing->quantity) }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Current Images -->
            @if($listing->media->count() > 0)
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3">Current Images</h3>
                <div class="grid grid-cols-3 gap-4">
                    @foreach($listing->media as $media)
                        <div class="relative">
                            <img src="{{ $media->data_uri }}"
                                 alt="Listing image"
                                 class="w-full h-32 object-contain bg-gray-50 border border-gray-200 rounded-lg p-2">
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-gray-500">Note: Images cannot be changed after creation</p>
            </div>
            @endif

            <!-- Active Status -->
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $listing->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Listing is active</label>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="px-6 py-2.5 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors">
                    Update Listing
                </button>
                <a href="{{ route('vendor.listings.index') }}"
                   class="px-6 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
