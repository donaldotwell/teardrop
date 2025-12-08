@extends('layouts.app')
@section('page-title', 'My Disputes')

@section('breadcrumbs')
    <span class="text-gray-600">My Disputes</span>
@endsection

@section('page-heading')
    My Disputes
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Status Filter Tabs --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('disputes.index') }}"
                   class="px-4 py-2 rounded {{ !request('status') ? 'bg-yellow-100 text-yellow-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    All Disputes ({{ $statusCounts['all'] }})
                </a>
                <a href="{{ route('disputes.index', ['status' => 'open']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'open' ? 'bg-yellow-100 text-yellow-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Open ({{ $statusCounts['open'] }})
                </a>
                <a href="{{ route('disputes.index', ['status' => 'under_review']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'under_review' ? 'bg-yellow-100 text-yellow-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Under Review
                </a>
                <a href="{{ route('disputes.index', ['status' => 'resolved']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'resolved' ? 'bg-yellow-100 text-yellow-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Resolved ({{ $statusCounts['resolved'] }})
                </a>
                <a href="{{ route('disputes.index', ['status' => 'closed']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'closed' ? 'bg-yellow-100 text-yellow-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Closed ({{ $statusCounts['closed'] }})
                </a>
            </div>
        </div>

        {{-- Disputes List --}}
        <div class="space-y-4">
            @forelse($disputes as $dispute)
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:border-yellow-300">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $dispute->subject }}
                                </h3>

                                {{-- Status Badge --}}
                                @php
                                    $statusColor = $dispute->getStatusColor();
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                    {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                                </span>

                                {{-- Priority Badge --}}
                                @if($dispute->priority !== 'medium')
                                    @php
                                        $priorityColor = $dispute->getPriorityColor();
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $priorityColor }}-100 text-{{ $priorityColor }}-800">
                                        {{ ucfirst($dispute->priority) }} Priority
                                    </span>
                                @endif
                            </div>

                            <div class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Order:</span>
                                <a href="{{ route('orders.show', $dispute->order) }}" class="text-yellow-700 hover:text-yellow-800">
                                    #{{ substr($dispute->order->uuid, 0, 8) }}
                                </a>
                                - {{ $dispute->order->listing->title }}
                            </div>

                            <div class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Type:</span> {{ ucfirst(str_replace('_', ' ', $dispute->type)) }}
                            </div>

                            <div class="text-sm text-gray-600">
                                <span class="font-medium">Amount:</span> ${{ number_format($dispute->disputed_amount, 2) }}
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-sm text-gray-500 mb-2">
                                {{ $dispute->created_at->format('M d, Y') }}
                            </div>
                            <div class="text-sm text-gray-500 mb-4">
                                {{ $dispute->created_at->diffForHumans() }}
                            </div>
                            <a href="{{ route('disputes.show', $dispute) }}"
                               class="inline-block px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                View Details
                            </a>
                        </div>
                    </div>

                    {{-- Other Party Info --}}
                    <div class="pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                @if($dispute->initiated_by === auth()->id())
                                    <span class="text-sm text-gray-600">Dispute against:</span>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <span class="text-yellow-700 font-medium text-xs">{{ substr($dispute->disputedAgainst->username_pub, 0, 1) }}</span>
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $dispute->disputedAgainst->username_pub }}</span>
                                        <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">
                                            Vendor
                                        </span>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-600">Dispute by:</span>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <span class="text-yellow-700 font-medium text-xs">{{ substr($dispute->initiatedBy->username_pub, 0, 1) }}</span>
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $dispute->initiatedBy->username_pub }}</span>
                                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">
                                            Buyer
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Unread Messages Indicator --}}
                            @php
                                $unreadCount = $dispute->messages()
                                    ->where('user_id', '!=', auth()->id())
                                    ->where('is_internal', false)
                                    ->where('is_read', false)
                                    ->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $unreadCount }} new message{{ $unreadCount > 1 ? 's' : '' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
                    <div class="text-gray-500 mb-4">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-gray-400 text-2xl">⚖️</span>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Disputes Found</h3>
                        <p class="text-gray-600">
                            @if(request('status'))
                                No disputes with status "{{ request('status') }}" found.
                            @else
                                You haven't created any disputes yet.
                            @endif
                        </p>
                    </div>

                    @if(!request('status'))
                        <div class="mt-6">
                            <a href="{{ route('orders.index') }}"
                               class="inline-block px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                View My Orders
                            </a>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($disputes->hasPages())
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                {{ $disputes->links() }}
            </div>
        @endif

        {{-- Info Box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-sm font-medium text-blue-800 mb-2">About Disputes</h3>
            <div class="text-sm text-blue-700 space-y-2">
                <p>• You can create a dispute for completed orders if there are issues with your purchase.</p>
                <p>• Disputes help protect both buyers and vendors through our resolution process.</p>
                <p>• For escrow orders, funds are held until the dispute is resolved.</p>
                <p>• Upload evidence and communicate with the other party to help resolve issues quickly.</p>
            </div>
        </div>
    </div>
@endsection
