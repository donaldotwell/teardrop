@extends('layouts.vendor')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Customer Reviews</h1>
                <p class="text-sm text-gray-600 mt-1">See what your customers are saying</p>
            </div>
            @if($reviews->count() > 0)
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-700">
                    {{ number_format($reviews->avg(function($review) {
                        return ($review->rating_stealth + $review->rating_quality + $review->rating_delivery) / 3;
                    }), 1) }}
                </div>
                <div class="text-xs text-gray-500">Average Rating</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Reviews List -->
    @if($reviews->count() > 0)
        <div class="space-y-4">
            @foreach($reviews as $review)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6">
                        <!-- Review Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center shrink-0">
                                    <span class="text-lg font-bold text-purple-700">
                                        {{ strtoupper(substr($review->user->username_pub, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $review->user->username_pub }}</div>
                                    <div class="text-sm text-gray-500">{{ $review->created_at->format('F d, Y') }}</div>
                                    <a href="{{ route('listings.show', $review->listing) }}"
                                       class="text-sm text-purple-600 hover:text-purple-800 mt-1 inline-block">
                                        {{ $review->listing->title }}
                                    </a>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-purple-700">
                                    {{ number_format(($review->rating_stealth + $review->rating_quality + $review->rating_delivery) / 3, 1) }}
                                </div>
                                <div class="text-xs text-gray-500">Overall</div>
                            </div>
                        </div>

                        <!-- Rating Breakdown -->
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="bg-purple-50 rounded-lg p-3 text-center">
                                <div class="text-lg font-bold text-purple-700">{{ $review->rating_stealth }}</div>
                                <div class="text-xs text-gray-600">Stealth</div>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-3 text-center">
                                <div class="text-lg font-bold text-purple-700">{{ $review->rating_quality }}</div>
                                <div class="text-xs text-gray-600">Quality</div>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-3 text-center">
                                <div class="text-lg font-bold text-purple-700">{{ $review->rating_delivery }}</div>
                                <div class="text-xs text-gray-600">Delivery</div>
                            </div>
                        </div>

                        <!-- Review Comment -->
                        @if($review->comment)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-sm text-gray-700">{{ $review->comment }}</p>
                        </div>
                        @endif

                        <!-- Buyer Price (if available) -->
                        @if($review->buyer_price)
                        <div class="mt-3 text-xs text-gray-500">
                            Purchase Price: ${{ number_format($review->buyer_price, 2) }}
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            {{ $reviews->links() }}
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="text-gray-400 text-lg mb-2">No Reviews Yet</div>
            <p class="text-sm text-gray-500">Customer reviews will appear here once your orders are completed and reviewed</p>
        </div>
    @endif
</div>
@endsection
