<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporarily Locked — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-amber-50 min-h-screen flex flex-col items-center justify-center p-4">

    {{-- Site name --}}
    <div class="mb-6 text-center">
        <span class="text-2xl font-bold text-amber-800 tracking-tight">{{ config('app.name') }}</span>
        <p class="text-xs text-amber-600 mt-0.5 uppercase tracking-widest">Secure Access</p>
    </div>

    <div class="w-full max-w-sm">

        <div class="bg-white border border-red-200 rounded-2xl shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="bg-red-600 px-6 py-4 text-center">
                <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-2">
                    <span class="text-white font-bold text-lg leading-none">!</span>
                </div>
                <h1 class="text-base font-semibold text-white">Access Temporarily Locked</h1>
                <p class="text-xs text-red-100 mt-0.5">Too many failed verification attempts</p>
            </div>

            <div class="px-6 py-5">

                {{-- Countdown --}}
                <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-4 text-center">
                    <p class="text-xs text-red-600 uppercase tracking-wide font-medium mb-1">Time remaining</p>
                    <p class="text-4xl font-bold text-red-700">{{ $remainingMinutes }}<span class="text-lg font-normal ml-1">min</span></p>
                </div>

                {{-- Info --}}
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-5 text-xs text-amber-800 space-y-1">
                    <p class="font-medium">What now?</p>
                    <p>Wait {{ $remainingMinutes }} minute{{ $remainingMinutes !== 1 ? 's' : '' }}, then return to try again.</p>
                    <p>You will receive 3 fresh attempts after the lockout expires.</p>
                </div>

                <form action="{{ route('bot-challenge') }}" method="GET">
                    <button type="submit"
                            class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl transition-colors text-sm">
                        Check Status
                    </button>
                </form>
            </div>

            <div class="border-t border-gray-100 px-6 py-3 bg-gray-50 text-xs text-gray-400 text-center">
                Lockout resets automatically after 30 minutes.
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-5">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </div>

</body>
</html>
