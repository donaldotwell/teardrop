@if($user->receivedReviews->isNotEmpty())
    <div class="space-y-4">
        @foreach($user->receivedReviews as $review)
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-sm font-medium text-gray-700">Anonymous Buyer</span>
                    <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                </div>

                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div class="text-center">
                        <div class="text-xs text-gray-600 mb-1">Stealth</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $review->rating_stealth }}/5</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-gray-600 mb-1">Quality</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $review->rating_quality }}/5</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-gray-600 mb-1">Delivery</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $review->rating_delivery }}/5</div>
                    </div>
                </div>

                <p class="text-sm text-gray-700 italic">{{ $review->comment }}</p>
            </div>
        @endforeach
    </div>
@else
    <div class="py-12 text-center">
        <p class="text-gray-500">No reviews yet for this vendor.</p>
    </div>
@endif
