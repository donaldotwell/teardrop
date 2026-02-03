@extends('layouts.vendor')

@section('content')
<div class="space-y-6">
    <!-- Header with Create Button -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Listings</h1>
                <p class="text-sm text-gray-600 mt-1">Manage your product listings</p>
            </div>
            <a href="{{ route('vendor.listings.create') }}"
               class="px-6 py-2.5 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition-colors">
                Create New Listing
            </a>
        </div>
    </div>

    <!-- Listings Grid -->
    @if($listings->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($listings as $listing)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <!-- Image Gallery -->
                    <x-image-gallery
                        :images="$listing->media"
                        :title="$listing->title"
                        :modal-id="'listing-gallery-vendor-' . $listing->id"
                    />

                    <!-- Content -->
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">{{ $listing->title }}</h3>
                            @if($listing->is_featured)
                                <span class="ml-2 shrink-0 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Featured</span>
                            @endif
                        </div>

                        <div class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $listing->short_description }}</div>

                        <div class="flex items-center justify-between mb-3">
                            <div class="text-xl font-bold text-purple-700">${{ number_format($listing->price, 2) }}</div>
                            <div class="text-sm text-gray-500">
                                @if($listing->quantity === null)
                                    <span class="text-green-600">Unlimited</span>
                                @else
                                    @php $availableStock = $listing->getAvailableStock(); @endphp
                                    Stock: {{ $availableStock }}
                                    @if($availableStock <= 0)
                                        <span class="text-red-600">(OUT)</span>
                                    @elseif($availableStock <= 5)
                                        <span class="text-orange-600">(Low)</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mb-3 text-xs">
                            @if($listing->is_active)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">Active</span>
                            @else
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full">Inactive</span>
                            @endif
                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full">{{ $listing->views }} views</span>
                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full uppercase">{{ $listing->payment_method }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('vendor.listings.edit', $listing) }}"
                               class="text-center py-2 text-sm bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors">
                                Edit
                            </a>
                            <form action="{{ route('vendor.listings.toggle-status', $listing) }}" method="post" class="inline">
                                @csrf
                                <button type="submit"
                                        class="w-full py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                    {{ $listing->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </div>

                        @if(!$listing->is_featured)
                        <div class="mt-2">
                            <a href="{{ route('vendor.listings.feature-form', $listing) }}"
                               class="block text-center py-2 text-sm bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors">
                                Feature This Listing
                            </a>
                        </div>
                        @endif

                        <div class="mt-2">
                            <form action="{{ route('vendor.listings.destroy', $listing) }}" method="post"
                                  onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full py-2 text-sm bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            {{ $listings->links() }}
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="text-gray-400 text-lg mb-2">No Listings Yet</div>
            <p class="text-sm text-gray-500 mb-6">Create your first listing to start selling</p>
            <a href="{{ route('vendor.listings.create') }}"
               class="inline-block px-6 py-2.5 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition-colors">
                Create New Listing
            </a>
        </div>
    @endif
</div>
@endsection
