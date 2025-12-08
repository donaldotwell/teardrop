@if($reviews->isEmpty())
    <div class="text-center py-12">
        <div class="text-gray-400 mb-2">No reviews yet</div>
        <p class="text-sm text-gray-500">Be the first to review this listing</p>
    </div>
@else
    <div class="space-y-6">
        @foreach($reviews as $review)
            <div class="border-b border-gray-200 pb-6 last:border-0">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $review->user->username_pub }}</div>
                        <div class="text-sm text-gray-500">{{ $review->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-600 mb-1">Stealth</div>
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-{{ $i <= $review->rating_stealth ? 'yellow' : 'gray' }}-400">★</span>
                            @endfor
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-600 mb-1">Quality</div>
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-{{ $i <= $review->rating_quality ? 'yellow' : 'gray' }}-400">★</span>
                            @endfor
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-600 mb-1">Delivery</div>
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-{{ $i <= $review->rating_delivery ? 'yellow' : 'gray' }}-400">★</span>
                            @endfor
                        </div>
                    </div>
                </div>
                
                @if($review->comment)
                    <div class="text-gray-700">{{ $review->comment }}</div>
                @endif
            </div>
        @endforeach
    </div>
@endif
