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
    <div class="bg-white shadow rounded-lg overflow-hidden border border-blue-200">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-200">
            <h2 class="text-2xl font-bold text-blue-900">General Harm Reduction Guidelines</h2>
        </div>
        
        <div class="p-6">
            <ul class="space-y-4 text-gray-800">
                <li class="flex items-start gap-3">
                    <span class="text-blue-600 text-xl font-bold">-</span>
                    <div>
                        <strong>Start Low, Go Slow:</strong> Always start with the lowest possible dose, especially with new substances or vendors. You can always take more, but you cannot take less.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-blue-600 text-xl font-bold">-</span>
                    <div>
                        <strong>Test Your Substances:</strong> Use reagent testing kits (Marquis, Mecke, Mandelin) to verify substance identity. Fentanyl test strips are essential for opioids.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-blue-600 text-xl font-bold">-</span>
                    <div>
                        <strong>Never Use Alone:</strong> Have a trusted friend present who can call for help if needed. Consider using "Never Use Alone" hotlines (800-484-3731 in US).
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-blue-600 text-xl font-bold">-</span>
                    <div>
                        <strong>Know Drug Interactions:</strong> Research interactions between substances. Many combinations can be fatal (e.g., opioids + benzodiazepines, stimulants + depressants).
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-blue-600 text-xl font-bold">-</span>
                    <div>
                        <strong>Have Naloxone Available:</strong> Keep Narcan (naloxone) on hand if using opioids. It reverses overdoses and saves lives. Available free at many pharmacies.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-blue-600 text-xl font-bold">-</span>
                    <div>
                        <strong>Stay Hydrated:</strong> Drink water regularly, especially with stimulants or MDMA. But don't overdo it - hyponatremia is real.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-blue-600 text-xl font-bold">-</span>
                    <div>
                        <strong>Safe Environment:</strong> Use in a comfortable, safe place. Have emergency contacts ready. Know your exit plan.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-blue-600 text-xl font-bold">-</span>
                    <div>
                        <strong>Avoid Poly-Drug Use:</strong> Using multiple substances simultaneously increases risks exponentially. Stick to one at a time when possible.
                    </div>
                </li>
            </ul>
        </div>
    </div>

    {{-- Opioid Safety --}}
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
                <li class="flex items-start gap-2">
                    <span class="text-purple-600 font-bold">*</span>
                    <span><strong>Dose carefully:</strong> Tolerance drops rapidly. After days of abstinence, use 1/4 of your usual dose.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-purple-600 font-bold">*</span>
                    <span><strong>Keep Narcan accessible:</strong> Nasal spray Narcan can be administered by anyone. Have multiple doses ready.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-purple-600 font-bold">*</span>
                    <span><strong>Never mix with benzos or alcohol:</strong> This combination is the leading cause of overdose deaths.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-purple-600 font-bold">*</span>
                    <span><strong>Use the recovery position:</strong> If someone is unconscious but breathing, place them on their side.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-purple-600 font-bold">*</span>
                    <span><strong>Don't waste time:</strong> Call 911 immediately if someone is unresponsive. Good Samaritan laws protect you.</span>
                </li>
            </ul>
            
            <div class="mt-4 p-4 bg-purple-50 border border-purple-300 rounded">
                <p class="text-sm text-purple-900">
                    <strong>Signs of overdose:</strong> Slow/stopped breathing, blue lips or fingernails, cold/clammy skin, inability to wake up, 
                    gurgling or choking sounds. CALL 911 IMMEDIATELY.
                </p>
            </div>
        </div>
    </div>

    {{-- Stimulant Safety --}}
    <div class="bg-white shadow rounded-lg overflow-hidden border border-orange-200">
        <div class="px-6 py-4 bg-orange-50 border-b border-orange-200">
            <h2 class="text-2xl font-bold text-orange-900">Stimulant Safety</h2>
            <p class="text-sm text-orange-700 mt-1">Cocaine, amphetamine, methamphetamine, etc.</p>
        </div>
        
        <div class="p-6">
            <ul class="space-y-3 text-gray-800">
                <li class="flex items-start gap-2">
                    <span class="text-orange-600 font-bold">*</span>
                    <span><strong>Watch your heart:</strong> Stimulants strain the cardiovascular system. Take breaks. Don't redose compulsively.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-orange-600 font-bold">*</span>
                    <span><strong>Eat and sleep:</strong> Force yourself to eat nutritious food and get sleep. Stimulant psychosis is real.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-orange-600 font-bold">*</span>
                    <span><strong>Hydration is key:</strong> Drink water and electrolytes. Avoid overheating, especially in clubs/festivals.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-orange-600 font-bold">*</span>
                    <span><strong>Avoid mixing uppers and downers:</strong> Don't use stimulants to "balance" depressants. Both remain active and strain your body.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-orange-600 font-bold">*</span>
                    <span><strong>Practice safe snorting:</strong> Use clean straws (never shared). Rinse nose with saline after. Rotate nostrils.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-orange-600 font-bold">*</span>
                    <span><strong>Watch for psychosis:</strong> Paranoia, hallucinations, or erratic behavior means it's time to stop and sleep.</span>
                </li>
            </ul>
        </div>
    </div>

    {{-- Psychedelic Safety --}}
    <div class="bg-white shadow rounded-lg overflow-hidden border border-green-200">
        <div class="px-6 py-4 bg-green-50 border-b border-green-200">
            <h2 class="text-2xl font-bold text-green-900">Psychedelic Safety</h2>
            <p class="text-sm text-green-700 mt-1">LSD, psilocybin mushrooms, DMT, etc.</p>
        </div>
        
        <div class="p-6">
            <ul class="space-y-3 text-gray-800">
                <li class="flex items-start gap-2">
                    <span class="text-green-600 font-bold">*</span>
                    <span><strong>Set and setting:</strong> Use in a comfortable, safe place with trusted people. Your mental state matters.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-green-600 font-bold">*</span>
                    <span><strong>Have a trip sitter:</strong> Someone sober to provide reassurance and handle emergencies.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-green-600 font-bold">*</span>
                    <span><strong>Test your tabs:</strong> Use Ehrlich and Hoffman reagents to verify LSD. 25x-NBOMe compounds are dangerous.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-green-600 font-bold">*</span>
                    <span><strong>Don't fight it:</strong> Surrender to the experience. Resisting increases anxiety. Remember: it will end.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-green-600 font-bold">*</span>
                    <span><strong>Avoid if mentally unwell:</strong> Don't use if you have schizophrenia, bipolar disorder, or severe anxiety/depression.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-green-600 font-bold">*</span>
                    <span><strong>Integration is important:</strong> Process your experience afterwards. Psychedelics can be therapeutic but also overwhelming.</span>
                </li>
            </ul>
        </div>
    </div>

    {{-- Resources --}}
    <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Additional Resources</h2>
        </div>
        
        <div class="p-6">
            <div class="space-y-4 text-gray-800">
                <div>
                    <h3 class="font-bold text-lg mb-2">Crisis & Emergency Resources</h3>
                    <ul class="list-disc list-inside space-y-1 ml-4">
                        <li><strong>Never Use Alone Hotline:</strong> 1-800-484-3731 (US/Canada) - Call before using, they'll check on you</li>
                        <li><strong>National Poison Control:</strong> 1-800-222-1222 (US)</li>
                        <li><strong>SAMHSA National Helpline:</strong> 1-800-662-4357 - Free, confidential treatment referral</li>
                        <li><strong>Crisis Text Line:</strong> Text HOME to 741741</li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-bold text-lg mb-2">Harm Reduction Organizations</h3>
                    <ul class="list-disc list-inside space-y-1 ml-4">
                        <li><strong>DanceSafe</strong> - Drug checking and education services</li>
                        <li><strong>Erowid</strong> - Comprehensive drug information database</li>
                        <li><strong>PsychonautWiki</strong> - Detailed substance information and dosage guides</li>
                        <li><strong>TripSit</strong> - Online chat support for difficult experiences</li>
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
</div>
@endsection
