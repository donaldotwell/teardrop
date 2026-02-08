@extends($layout ?? 'layouts.app')

@section('title', 'Site Rules & Guidelines')

@section('page-heading')
    <h1 class="text-3xl font-bold text-gray-900">Site Rules & Guidelines</h1>
    <p class="text-gray-600 mt-1">Community standards and marketplace policies</p>
@endsection

@section('content')
<div class="space-y-6">
    {{-- General Rules --}}
    @if(isset($rules['general']) && $rules['general']->count() > 0)
    <div class="bg-white shadow rounded-lg overflow-hidden border border-red-200">
        <div class="px-6 py-4 bg-red-50 border-b border-red-200">
            <h2 class="text-2xl font-bold text-red-900">General Rules</h2>
            <p class="text-sm text-red-700 mt-1">All users must comply with these rules</p>
        </div>
        
        <div class="p-6">
            <ol class="list-decimal list-inside space-y-4 text-gray-800">
                @foreach($rules['general'] as $rule)
                <li class="leading-relaxed">
                    <strong>{{ $rule->title }}:</strong> {{ $rule->content }}
                </li>
                @endforeach
            </ol>
        </div>
    </div>
    @endif

    {{-- Vendor Rules --}}
    @if(isset($rules['vendor']) && $rules['vendor']->count() > 0)
    <div class="bg-white shadow rounded-lg overflow-hidden border border-amber-200">
        <div class="px-6 py-4 bg-amber-50 border-b border-amber-200">
            <h2 class="text-2xl font-bold text-amber-900">Vendor Rules</h2>
            <p class="text-sm text-amber-700 mt-1">Additional requirements for vendors</p>
        </div>
        
        <div class="p-6">
            <ol class="list-decimal list-inside space-y-4 text-gray-800">
                @foreach($rules['vendor'] as $rule)
                <li class="leading-relaxed">
                    <strong>{{ $rule->title }}:</strong> {{ $rule->content }}
                </li>
                @endforeach
            </ol>
        </div>
    </div>
    @endif

    {{-- Buyer Rules --}}
    @if(isset($rules['buyer']) && $rules['buyer']->count() > 0)
    <div class="bg-white shadow rounded-lg overflow-hidden border border-blue-200">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-200">
            <h2 class="text-2xl font-bold text-blue-900">Buyer Rules</h2>
            <p class="text-sm text-blue-700 mt-1">Guidelines for safe purchasing</p>
        </div>
        
        <div class="p-6">
            <ol class="list-decimal list-inside space-y-4 text-gray-800">
                @foreach($rules['buyer'] as $rule)
                <li class="leading-relaxed">
                    <strong>{{ $rule->title }}:</strong> {{ $rule->content }}
                </li>
                @endforeach
            </ol>
        </div>
    </div>
    @endif

    {{-- Consequences --}}
    @if(isset($rules['consequences']) && $rules['consequences']->count() > 0)
    <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Consequences of Rule Violations</h2>
        </div>
        
        <div class="p-6">
            <div class="space-y-4 text-gray-800">
                @foreach($rules['consequences'] as $index => $rule)
                    @php
                        $badgeColors = [
                            'WARNING' => 'bg-yellow-100 text-yellow-800',
                            'SUSPENSION' => 'bg-orange-100 text-orange-800',
                            'PERMANENT BAN' => 'bg-red-100 text-red-800',
                        ];
                        $badgeColor = $badgeColors[$rule->title] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <div class="flex items-start gap-3">
                        <span class="px-3 py-1 {{ $badgeColor }} text-sm font-bold rounded">{{ $rule->title }}</span>
                        <p>{{ $rule->content }}</p>
                    </div>
                @endforeach
                <div class="mt-6 p-4 bg-amber-50 border border-amber-300 rounded">
                    <p class="text-sm text-amber-900">
                        <strong>Note:</strong> All disputes are reviewed by staff. Evidence must be provided. Staff decisions are final.
                        If you believe you were banned in error, you may submit a ticket from a new account with evidence.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Forum Rules --}}
    @if(isset($rules['forum']) && $rules['forum']->count() > 0)
    <div class="bg-white shadow rounded-lg overflow-hidden border border-purple-200">
        <div class="px-6 py-4 bg-purple-50 border-b border-purple-200">
            <h2 class="text-2xl font-bold text-purple-900">Forum Rules</h2>
        </div>
        
        <div class="p-6">
            <ul class="list-disc list-inside space-y-3 text-gray-800">
                @foreach($rules['forum'] as $rule)
                <li>{{ $rule->title }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</div>
@endsection
