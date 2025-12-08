@extends('layouts.admin')
@section('page-title', 'Disputes Management')

@section('breadcrumbs')
    <span class="text-gray-600">Disputes</span>
@endsection

@section('page-heading')
    Disputes Management
@endsection

@section('page-description')
    Monitor and manage all marketplace disputes and resolutions
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Total Disputes</div>
                <div class="text-2xl font-semibold text-gray-900">{{ $stats['total_disputes'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Open</div>
                <div class="text-2xl font-semibold text-yellow-600">{{ $stats['open_disputes'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Under Review</div>
                <div class="text-2xl font-semibold text-blue-600">{{ $stats['under_review_disputes'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Escalated</div>
                <div class="text-2xl font-semibold text-red-600">{{ $stats['escalated_disputes'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Resolved</div>
                <div class="text-2xl font-semibold text-green-600">{{ $stats['resolved_disputes'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Total Value</div>
                <div class="text-2xl font-semibold text-gray-900">${{ number_format($stats['total_value'] ?? 0, 2) }}</div>
            </div>
        </div>

        {{-- Filters and Search --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form method="GET" action="{{ route('admin.disputes.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    {{-- Search --}}
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text"
                               name="search"
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Subject, Order ID, username..."
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                                id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Statuses</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="waiting_vendor" {{ request('status') == 'waiting_vendor' ? 'selected' : '' }}>Waiting Vendor</option>
                            <option value="waiting_buyer" {{ request('status') == 'waiting_buyer' ? 'selected' : '' }}>Waiting Buyer</option>
                            <option value="escalated" {{ request('status') == 'escalated' ? 'selected' : '' }}>Escalated</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    {{-- Priority Filter --}}
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority"
                                id="priority"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Priorities</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>

                    {{-- Dispute Type Filter --}}
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type"
                                id="type"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Types</option>
                            <option value="item_not_received" {{ request('type') == 'item_not_received' ? 'selected' : '' }}>Item Not Received</option>
                            <option value="item_not_as_described" {{ request('type') == 'item_not_as_described' ? 'selected' : '' }}>Not As Described</option>
                            <option value="damaged_item" {{ request('type') == 'damaged_item' ? 'selected' : '' }}>Damaged Item</option>
                            <option value="wrong_item" {{ request('type') == 'wrong_item' ? 'selected' : '' }}>Wrong Item</option>
                            <option value="quality_issue" {{ request('type') == 'quality_issue' ? 'selected' : '' }}>Quality Issue</option>
                            <option value="shipping_issue" {{ request('type') == 'shipping_issue' ? 'selected' : '' }}>Shipping Issue</option>
                            <option value="vendor_unresponsive" {{ request('type') == 'vendor_unresponsive' ? 'selected' : '' }}>Vendor Unresponsive</option>
                            <option value="refund_request" {{ request('type') == 'refund_request' ? 'selected' : '' }}>Refund Request</option>
                            <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    {{-- Admin Assigned Filter --}}
                    <div>
                        <label for="assigned_admin" class="block text-sm font-medium text-gray-700 mb-1">Assigned Admin</label>
                        <select name="assigned_admin"
                                id="assigned_admin"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Admins</option>
                            <option value="unassigned" {{ request('assigned_admin') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            @foreach($admins ?? [] as $admin)
                                <option value="{{ $admin->id }}" {{ request('assigned_admin') == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->username_pub }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        Filter Disputes
                    </button>
                    <a href="{{ route('admin.disputes.index') }}"
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                        Clear Filters
                    </a>
                    <a href="{{ route('admin.disputes.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                       class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Export CSV
                    </a>
                </div>
            </form>
        </div>

        {{-- Disputes Table --}}
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Disputes ({{ $disputes->total() }} total)
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispute</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parties</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($disputes as $dispute)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 max-w-xs truncate">
                                    {{ $dispute->subject }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    #{{ substr($dispute->uuid, 0, 8) }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ ucfirst(str_replace('_', ' ', $dispute->type)) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                            <span class="text-blue-700 font-medium text-xs">{{ substr($dispute->initiatedBy->username_pub, 0, 1) }}</span>
                                        </div>
                                        <div class="text-sm">
                                            <span class="font-medium text-gray-900">{{ $dispute->initiatedBy->username_pub }}</span>
                                            <span class="text-xs text-blue-600 ml-1">(Buyer)</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center mr-2">
                                            <span class="text-red-700 font-medium text-xs">{{ substr($dispute->disputedAgainst->username_pub, 0, 1) }}</span>
                                        </div>
                                        <div class="text-sm">
                                            <span class="font-medium text-gray-900">{{ $dispute->disputedAgainst->username_pub }}</span>
                                            <span class="text-xs text-red-600 ml-1">(Vendor)</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    #{{ substr($dispute->order->uuid, 0, 8) }}
                                </div>
                                <div class="text-sm text-gray-500 max-w-xs truncate">
                                    {{ $dispute->order->listing->title }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    ${{ number_format($dispute->disputed_amount, 2) }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    of ${{ number_format($dispute->order->usd_price, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @switch($dispute->status)
                                        @case('open')
                                            bg-yellow-100 text-yellow-800
                                            @break
                                        @case('under_review')
                                            bg-blue-100 text-blue-800
                                            @break
                                        @case('waiting_vendor')
                                            bg-orange-100 text-orange-800
                                            @break
                                        @case('waiting_buyer')
                                            bg-purple-100 text-purple-800
                                            @break
                                        @case('escalated')
                                            bg-red-100 text-red-800
                                            @break
                                        @case('resolved')
                                            bg-green-100 text-green-800
                                            @break
                                        @case('closed')
                                            bg-gray-100 text-gray-800
                                            @break
                                        @default
                                            bg-gray-100 text-gray-800
                                    @endswitch">
                                        {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                                    </span>

                                    {{-- Priority Badge --}}
                                    @if($dispute->priority !== 'medium')
                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium
                                        @switch($dispute->priority)
                                            @case('low')
                                                bg-gray-100 text-gray-700
                                                @break
                                            @case('high')
                                                bg-orange-100 text-orange-700
                                                @break
                                            @case('urgent')
                                                bg-red-100 text-red-700
                                                @break
                                            @default
                                                bg-gray-100 text-gray-700
                                        @endswitch">
                                            {{ ucfirst($dispute->priority) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($dispute->assignedAdmin)
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-2">
                                            <span class="text-purple-700 font-medium text-xs">{{ substr($dispute->assignedAdmin->username_pub, 0, 1) }}</span>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">{{ $dispute->assignedAdmin->username_pub }}</div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $dispute->created_at->format('M d, Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $dispute->created_at->format('g:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.disputes.show', $dispute) }}"
                                       class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                        View
                                    </a>

                                    @if(!$dispute->assignedAdmin)
                                        <form action="{{ route('admin.disputes.assign', $dispute) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-purple-100 text-purple-700 rounded hover:bg-purple-200">
                                                Assign Me
                                            </button>
                                        </form>
                                    @endif

                                    @if($dispute->status === 'open' || $dispute->status === 'under_review')
                                        <form action="{{ route('admin.disputes.escalate', $dispute) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">
                                                Escalate
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                No disputes found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($disputes->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $disputes->links() }}
                </div>
            @endif
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Dispute Activity</h3>

            <div class="space-y-3">
                @forelse($recent_activity ?? [] as $activity)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 rounded-full
                                {{ $activity['type'] === 'created' ? 'bg-yellow-500' : '' }}
                                {{ $activity['type'] === 'resolved' ? 'bg-green-500' : '' }}
                                {{ $activity['type'] === 'escalated' ? 'bg-red-500' : '' }}
                                {{ $activity['type'] === 'assigned' ? 'bg-blue-500' : '' }}">
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $activity['message'] }}</div>
                                <div class="text-sm text-gray-500">{{ $activity['time'] }}</div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            ${{ number_format($activity['amount'], 2) }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-4">
                        No recent activity to display.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
