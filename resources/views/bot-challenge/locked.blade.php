<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locked Out - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <noscript>
        <style>.js-warning { display: none; }</style>
    </noscript>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">

            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-red-700 font-bold text-2xl">X</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Temporarily Locked</h1>
                <p class="text-sm text-gray-600">Too many failed verification attempts</p>
            </div>

            {{-- Lockout Message --}}
            <div class="bg-red-50 border border-red-200 rounded p-6 mb-6">
                <div class="text-center">
                    <p class="text-lg font-semibold text-red-900 mb-3">
                        You have been temporarily locked out
                    </p>
                    <p class="text-sm text-red-800 mb-4">
                        After 3 failed verification attempts, access is restricted for security reasons.
                    </p>
                    <div class="bg-white rounded p-4 border border-red-200">
                        <p class="text-xs text-gray-600 mb-1">Time remaining:</p>
                        <p class="text-2xl font-bold text-red-700">
                            {{ $remainingMinutes }} minute{{ $remainingMinutes !== 1 ? 's' : '' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- What to do --}}
            <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6">
                <h3 class="font-semibold text-blue-900 mb-2 text-sm">What can you do?</h3>
                <ul class="text-xs text-blue-800 space-y-2 list-disc list-inside">
                    <li>Wait for the lockout period to expire</li>
                    <li>Return to this page after {{ $remainingMinutes }} minutes</li>
                    <li>You will get 3 new attempts after the lockout expires</li>
                </ul>
            </div>

            {{-- Refresh Button --}}
            <form action="{{ route('bot-challenge') }}" method="get">
                <button type="submit"
                        class="w-full px-6 py-3 bg-gray-600 text-white font-semibold rounded hover:bg-gray-700 transition">
                    Check Status
                </button>
            </form>

            {{-- Security Info --}}
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="text-xs text-gray-600 space-y-2">
                    <p><strong>Why am I seeing this?</strong></p>
                    <p>
                        This security measure protects {{ config('app.name') }} from automated bots
                        and malicious activity. The lockout will automatically expire after 30 minutes.
                    </p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>

</body>
</html>
