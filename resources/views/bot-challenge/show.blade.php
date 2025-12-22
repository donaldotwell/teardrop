<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Verification - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <noscript>
        <style>.js-warning { display: none; }</style>
    </noscript>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        {{-- Main Challenge Card --}}
        <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">

            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-yellow-700 font-bold text-2xl">SEC</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Security Verification</h1>
                <p class="text-sm text-gray-600">Please complete the challenge below to continue</p>
            </div>

            {{-- Error Messages --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded p-4 mb-6">
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-800">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Remaining Attempts Warning --}}
            @if($remainingAttempts < 3)
                <div class="bg-amber-50 border border-amber-200 rounded p-4 mb-6">
                    <p class="text-sm text-amber-800 font-semibold">
                        WARNING: You have {{ $remainingAttempts }} attempt(s) remaining.
                        After 3 failed attempts, you will be locked out for 30 minutes.
                    </p>
                </div>
            @endif

            {{-- Math Challenge --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Solve the math problem:
                </label>

                <div class="flex justify-center mb-4">
                    <img src="{{ route('bot-challenge.image') }}"
                         alt="Math Challenge"
                         class="border-2 border-gray-300 rounded"
                         width="200"
                         height="80">
                </div>

                <p class="text-xs text-gray-500 text-center mb-4">
                    Can't see the image? Refresh the page to get a new challenge.
                </p>
            </div>

            {{-- Answer Form --}}
            <form action="{{ route('bot-challenge.verify') }}" method="post">
                @csrf

                <div class="mb-6">
                    <label for="answer" class="block text-sm font-medium text-gray-700 mb-2">
                        Your Answer:
                    </label>
                    <input type="number"
                           name="answer"
                           id="answer"
                           class="block w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 text-lg"
                           placeholder="Enter the result"
                           required
                           autofocus>
                </div>

                <button type="submit"
                        class="w-full px-6 py-3 bg-yellow-600 text-white font-semibold rounded hover:bg-yellow-700 transition">
                    Verify
                </button>
            </form>

            {{-- Security Info --}}
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="text-xs text-gray-600 space-y-2">
                    <p><strong>Security measures:</strong></p>
                    <ul class="list-disc list-inside ml-2 space-y-1">
                        <li>Verification expires after 30 minutes</li>
                        <li>Maximum 3 attempts allowed</li>
                        <li>Lockout period: 30 minutes after failed attempts</li>
                    </ul>
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
