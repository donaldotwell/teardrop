@extends($layout ?? 'layouts.app')

@section('title', 'Canary')

@section('page-heading')
    <h1 class="text-3xl font-bold text-gray-900">Canary</h1>
    <p class="text-gray-600 mt-1">Transparency notice signed by market administrators</p>
@endsection

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-900">Latest Canary</h2>
        @if($canary)
            <p class="text-sm text-gray-600 mt-1">
                Published: {{ $canary->created_at->format('F j, Y \a\t g:i A') }} UTC
            </p>
        @endif
    </div>
    
    <div class="p-6">
        @if($canary)
            <div class="bg-gray-50 rounded border border-gray-300 p-6">
                <pre class="whitespace-pre-wrap font-mono text-sm text-gray-800 break-words">{{ $canary->message }}</pre>
            </div>
            
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">
                <p class="text-sm text-blue-800">
                    <strong>Verification:</strong> This message is PGP-signed by the market administrators. 
                    Verify the signature using the admin PGP keys available on the 
                    <a href="{{ route('market-keys') }}" class="underline hover:text-blue-600">Market Staff Keys</a> page.
                </p>
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">No canary published yet.</p>
                <p class="text-gray-400 text-sm mt-2">Check back later for transparency updates.</p>
            </div>
        @endif
    </div>
</div>

<div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-6">
    <h3 class="text-lg font-bold text-amber-900 mb-3">What is a Canary?</h3>
    <p class="text-sm text-amber-800 mb-3">
        If the canary is NOT updated regularly or 
        disappears, assume the service may be compromised.
    </p>
    <p class="text-sm text-amber-800">
        We publish signed canaries regularly to maintain transparency with our community. Always verify the 
        PGP signature to ensure authenticity.
    </p>
</div>
@endsection
