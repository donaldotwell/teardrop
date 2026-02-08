@extends($layout ?? 'layouts.app')

@section('title', 'Harm Reduction')

@section('page-heading')
    <h1 class="text-3xl font-bold text-gray-900">Harm Reduction</h1>
    <p class="text-gray-600 mt-1">Safety information and responsible use guidelines</p>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Important Notice --}}
    <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded">
        <div class="flex items-start gap-3">
            <div class="text-red-600 text-3xl font-bold">!</div>
            <div>
                <h3 class="text-lg font-bold text-red-900 mb-2">This Marketplace Does Not Endorse Drug Use</h3>
                <p class="text-red-800 leading-relaxed">
                    All substances carry risks. We strongly encourage you to reconsider using drugs. However, if you choose to use,
                    please follow these harm reduction practices to minimize risks to your health and safety.
                </p>
            </div>
        </div>
    </div>

    {{-- General Harm Reduction --}}
    @if(isset($contents['general']) && $contents['general']->count() > 0)
    <div class="bg-white shadow rounded-lg overflow-hidden border border-blue-200">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-200">
            <h2 class="text-2xl font-bold text-blue-900">General Harm Reduction Guidelines</h2>
        </div>
        
        <div class="p-6">
            <ul class="space-y-4 text-gray-800">
                @foreach($contents['general'] as $content)
                <li class="flex items-start gap-3">
                    <span class="text-blue-600 text-xl font-bold">-</span>
                    <div>
                        <strong>{{ $content->title }}:</strong> {{ $content->content }}
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Opioid Safety --}}
    @if(isset($contents['opioid']) && $contents['opioid']->count() > 0)
    <div class="bg-white shadow rounded-lg overflow-hidden border border-purple-200">
        <div class="px-6 py-4 bg-purple-50 border-b border-purple-200">
            <h2 class="text-2xl font-bold text-purple-900">Opioid Safety</h2>
            <p class="text-sm text-purple-700 mt-1">Heroin, oxycodone, fentanyl, morphine, etc.</p>
        </div>
        
        <div class="p-6 space-y-4">
            <div class="p-4 bg-red-50 border border-red-300 rounded">
                <p class="text-red-900 font-bold">CRITICAL: Fentanyl and analogs are extremely potent and deadly. Always test for fentanyl.</p>
            </div>
            
            <ul class="space-y-3 text-gray-800">
                @foreach($contents['opioid'] as $content)
                <li class="flex items-start gap-2">
                    <span class="text-purple-600 font-bold">*</span>
                    <span><strong>{{ $content->title }}:</strong> {{ $content->content }}</span>
                </li>
                @endforeach
            </ul>
            
            <div class="mt-4 p-4 bg-purple-50 border border-purple-300 rounded">
                <p class="text-sm text-purple-900">
                    <strong>Signs of overdose:</strong> Slow/stopped breathing, blue lips or fingernails, cold/clammy skin, inability to wake up, 
                    gurgling or choking sounds. CALL 911 IMMEDIATELY.
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Stimulant Safety --}}
    @if(isset($contents['stimulant']) && $contents['stimulant']->count() > 0)
    <div class="bg-white shadow rounded-lg overflow-hidden border border-orange-200">
        <div class="px-6 py-4 bg-orange-50 border-b border-orange-200">
            <h2 class="text-2xl font-bold text-orange-900">Stimulant Safety</h2>
            <p class="text-sm text-orange-700 mt-1">Cocaine, amphetamine, methamphetamine, etc.</p>
        </div>
        
        <div class="p-6">
            <ul class="space-y-3 text-gray-800">
                @foreach($contents['stimulant'] as $content)
                <li class="flex items-start gap-2">
                    <span class="text-orange-600 font-bold">*</span>
                    <span><strong>{{ $content->title }}:</strong> {{ $content->content }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Psychedelic Safety --}}
    @if(isset($contents['psychedelic']) && $contents['psychedelic']->count() > 0)
    <div class="bg-white shadow rounded-lg overflow-hidden border border-green-200">
        <div class="px-6 py-4 bg-green-50 border-b border-green-200">
            <h2 class="text-2xl font-bold text-green-900">Psychedelic Safety</h2>
            <p class="text-sm text-green-700 mt-1">LSD, psilocybin mushrooms, DMT, etc.</p>
        </div>
        
        <div class="p-6">
            <ul class="space-y-3 text-gray-800">
                @foreach($contents['psychedelic'] as $content)
                <li class="flex items-start gap-2">
                    <span class="text-green-600 font-bold">*</span>
                    <span><strong>{{ $content->title }}:</strong> {{ $content->content }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Resources --}}
    @if(isset($contents['resources']) && $contents['resources']->count() > 0)
    <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Additional Resources</h2>
        </div>
        
        <div class="p-6">
            <div class="space-y-4 text-gray-800">
                <div>
                    <h3 class="font-bold text-lg mb-2">Crisis & Emergency Resources</h3>
                    <ul class="list-disc list-inside space-y-1 ml-4">
                        @foreach($contents['resources']->take(4) as $content)
                        <li><strong>{{ $content->title }}:</strong> {{ $content->content }}</li>
                        @endforeach
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-bold text-lg mb-2">Harm Reduction Organizations</h3>
                    <ul class="list-disc list-inside space-y-1 ml-4">
                        @foreach($contents['resources']->skip(4) as $content)
                        <li><strong>{{ $content->title }}</strong> - {{ $content->content }}</li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="mt-6 p-4 bg-blue-50 border border-blue-300 rounded">
                    <p class="text-sm text-blue-900">
                        <strong>Remember:</strong> No drug use is completely safe, but informed use is safer use. 
                        Stay educated, stay cautious, and look out for your community.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
