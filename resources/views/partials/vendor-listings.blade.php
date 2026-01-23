@if($user->listings->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($user->listings as $listing)
            <a href="{{ route('listings.show', $listing) }}"
               class="block group bg-white border-2 border-gray-200 rounded-lg overflow-hidden hover:border-amber-400 transition-all">
                <x-image-gallery
                    :images="$listing->media"
                    :title="$listing->title"
                    :modal-id="'listing-gallery-vendor-profile-' . $listing->id"
                />
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 group-hover:text-amber-600 transition-colors line-clamp-2 mb-2">
                        {{ $listing->title }}
                    </h3>
                    <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $listing->short_description }}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-amber-600">${{ number_format($listing->price, 2) }}</span>
                        <span class="text-xs text-gray-500">
                            @if($listing->quantity === null)
                                Unlimited
                            @else
                                {{ $listing->getAvailableStock() }} in stock
                            @endif
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@else
    <div class="py-12 text-center">
        <p class="text-gray-500">This vendor currently has no active listings.</p>
    </div>
@endif
