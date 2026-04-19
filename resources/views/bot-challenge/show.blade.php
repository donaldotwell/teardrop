<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Verification — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-amber-50 min-h-screen flex flex-col items-center justify-center p-4">

    {{-- Site name --}}
    <div class="mb-6 text-center">
        <span class="text-2xl font-bold text-amber-800 tracking-tight">{{ config('app.name') }}</span>
        <p class="text-xs text-amber-600 mt-0.5 uppercase tracking-widest">Secure Access</p>
    </div>

    <div class="w-full max-w-lg">

        {{-- Main challenge card --}}
        <div class="bg-white border border-amber-200 rounded-2xl shadow-sm overflow-hidden">

            {{-- Card header --}}
            <div class="bg-amber-600 px-6 py-4">
                <h1 class="text-base font-semibold text-white">Human Verification</h1>
                <p class="text-xs text-amber-100 mt-0.5">Identify the missing characters in the address</p>
            </div>

            <div class="px-6 py-5">

                {{-- Errors --}}
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-4">
                        @foreach($errors->all() as $error)
                            <p class="text-sm text-red-700">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- Attempts warning --}}
                @if($remainingAttempts < 3)
                    <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-4">
                        <p class="text-sm text-amber-800 font-medium">
                            {{ $remainingAttempts }} attempt{{ $remainingAttempts !== 1 ? 's' : '' }} remaining before lockout.
                        </p>
                    </div>
                @endif

                {{-- Challenge image --}}
                <div class="mb-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Site Address</p>
                    <div class="bg-gray-900 rounded-xl p-3 flex items-center justify-center overflow-x-auto">
                        <img src="{{ route('bot-challenge.image') }}"
                             alt="Security challenge — enter the highlighted characters"
                             class="max-w-full h-auto"
                             style="image-rendering: pixelated;">
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">
                        Enter the <span class="text-amber-600 font-semibold">highlighted</span> characters in order, left to right.
                    </p>
                </div>

                {{-- Answer form --}}
                <form action="{{ route('bot-challenge.verify') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="answer" class="block text-sm font-medium text-gray-700 mb-2 text-center">
                            6 missing characters
                        </label>
                        <input
                            type="text"
                            id="answer"
                            name="answer"
                            maxlength="6"
                            placeholder="· · · · · ·"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="off"
                            spellcheck="false"
                            value="{{ old('answer') }}"
                            class="block w-full max-w-[200px] mx-auto border-2 border-gray-200 rounded-xl px-4 py-3 text-xl text-center font-mono tracking-[0.5em] bg-white focus:outline-none focus:border-amber-500 @error('answer') border-red-400 @enderror"
                            required
                            autofocus>
                    </div>

                    <button type="submit"
                            class="w-full py-3 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold rounded-xl transition-colors text-sm">
                        Verify &rarr;
                    </button>
                </form>
            </div>

            {{-- Card footer --}}
            <div class="border-t border-gray-100 px-6 py-3 bg-gray-50 flex items-center justify-between text-xs text-gray-400">
                <a href="{{ route('bot-challenge') }}" class="hover:text-amber-600 transition-colors">
                    New challenge
                </a>
                <span>{{ $remainingAttempts }}/3 attempts left</span>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="mt-4 flex justify-center gap-4 text-sm">
            <a href="{{ route('login') }}"
               class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:border-amber-300 hover:text-amber-700 transition-colors shadow-sm">
                Sign In
            </a>
            <a href="{{ route('register') }}"
               class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors shadow-sm font-medium">
                Create Account
            </a>
        </div>

        {{-- Feature strip --}}
        <div class="mt-5 grid grid-cols-4 gap-2 text-center text-xs text-gray-500">
            <div class="bg-white border border-gray-100 rounded-lg py-2 px-1 shadow-sm">
                <div class="font-semibold text-gray-700 text-xs mb-0.5">Bitcoin</div>
                BTC
            </div>
            <div class="bg-white border border-gray-100 rounded-lg py-2 px-1 shadow-sm">
                <div class="font-semibold text-gray-700 text-xs mb-0.5">Monero</div>
                XMR
            </div>
            <div class="bg-white border border-gray-100 rounded-lg py-2 px-1 shadow-sm">
                <div class="font-semibold text-gray-700 text-xs mb-0.5">Escrow</div>
                Protected
            </div>
            <div class="bg-white border border-gray-100 rounded-lg py-2 px-1 shadow-sm">
                <div class="font-semibold text-gray-700 text-xs mb-0.5">PGP</div>
                Encrypted
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-5">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </div>

</body>
</html>
