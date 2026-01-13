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

        {{-- Platform Branding --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ config('app.name') }}</h1>
            <p class="text-gray-600">Secure Marketplace</p>
        </div>

        {{-- Main Challenge Card --}}
        <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200 mb-6">

            {{-- Header --}}
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-2">Security Verification</h2>
                <p class="text-sm text-gray-600">Verify the URL to prove you're human</p>
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
                        WARNING: {{ $remainingAttempts }} attempt(s) remaining.
                    </p>
                </div>
            @endif

            {{-- URL Verification Challenge --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3 text-center">
                    URL Verification Challenge
                </label>

                <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4">
                    <p class="text-xs text-blue-800 text-center">
                        <strong>Verify the URL below matches the image</strong><br>
                        Fill in the missing characters (marked with _) in order from left to right
                    </p>
                </div>

                <div class="flex justify-center mb-4">
                    <img src="{{ route('bot-challenge.image') }}"
                         alt="URL Challenge"
                         class="border-2 border-gray-300 rounded"
                         width="600"
                         height="100">
                </div>

                <p class="text-xs text-gray-500 text-center mb-4">
                    Refresh the page for a new challenge
                </p>
            </div>

            {{-- Answer Form --}}
            <form action="{{ route('bot-challenge.verify') }}" method="post">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-4 text-center">
                        Enter the 6 missing characters:
                    </label>

                    <div class="flex justify-center gap-3">
                        <input type="text"
                               name="char_0"
                               id="char_0"
                               maxlength="1"
                               class="w-12 h-14 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-400 text-2xl text-center font-bold lowercase bg-white"
                               required
                               autofocus>
                        <input type="text"
                               name="char_1"
                               id="char_1"
                               maxlength="1"
                               class="w-12 h-14 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-400 text-2xl text-center font-bold lowercase bg-white"
                               required>
                        <input type="text"
                               name="char_2"
                               id="char_2"
                               maxlength="1"
                               class="w-12 h-14 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-400 text-2xl text-center font-bold lowercase bg-white"
                               required>
                        <input type="text"
                               name="char_3"
                               id="char_3"
                               maxlength="1"
                               class="w-12 h-14 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-400 text-2xl text-center font-bold lowercase bg-white"
                               required>
                        <input type="text"
                               name="char_4"
                               id="char_4"
                               maxlength="1"
                               class="w-12 h-14 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-400 text-2xl text-center font-bold lowercase bg-white"
                               required>
                        <input type="text"
                               name="char_5"
                               id="char_5"
                               maxlength="1"
                               class="w-12 h-14 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-400 text-2xl text-center font-bold lowercase bg-white"
                               required>
                    </div>
                </div>

                <button type="submit"
                        class="w-full px-6 py-3 bg-yellow-600 text-white font-semibold rounded hover:bg-yellow-700 transition">
                    Verify URL
                </button>
            </form>
        </div>

        {{-- Platform Features - Subtle --}}
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4 text-center">About This Marketplace</h3>

            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <div class="text-xs font-semibold text-gray-900 mb-1">Bitcoin</div>
                    <div class="text-xs text-gray-600">BTC Payments</div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-900 mb-1">Monero</div>
                    <div class="text-xs text-gray-600">XMR Privacy</div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-900 mb-1">Escrow</div>
                    <div class="text-xs text-gray-600">Buyer Protection</div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-900 mb-1">PGP</div>
                    <div class="text-xs text-gray-600">Encrypted Chat</div>
                </div>
            </div>
        </div>

        {{-- Account Links --}}
        <div class="text-center space-y-3">
            <div class="flex gap-3 justify-center">
                <a href="{{ route('register') }}"
                   class="px-6 py-2 border border-yellow-600 text-yellow-700 font-medium rounded hover:bg-yellow-50 transition text-sm">
                    Create Account
                </a>
                <a href="{{ route('login') }}"
                   class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded hover:bg-gray-50 transition text-sm">
                    Sign In
                </a>
            </div>

            <p class="text-xs text-gray-500 mt-4">
                This challenge protects against automated bots
            </p>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-8">
            <p class="text-xs text-gray-500">
                © {{ date('Y') }} {{ config('app.name') }}
            </p>
        </div>
    </div>

</body>
</html>
