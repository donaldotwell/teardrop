<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Verification — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-2xl w-full">

        {{-- Branding --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-1">{{ config('app.name') }}</h1>
            <p class="text-sm text-gray-500">Secure Marketplace</p>
        </div>

        {{-- Challenge Card --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-6">

            <div class="text-center mb-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Security Verification</h2>
                <p class="text-sm text-gray-500">Type the missing characters from the address below</p>
            </div>

            {{-- Error --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-5">
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-800">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Attempts warning --}}
            @if($remainingAttempts < 3)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-5">
                    <p class="text-sm text-amber-800 font-medium">
                        {{ $remainingAttempts }} attempt(s) remaining before lockout.
                    </p>
                </div>
            @endif

            {{-- Masked URL display --}}
            <div class="mb-6">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2 text-center">Site Address</p>
                <div class="bg-gray-900 rounded-lg px-5 py-4 overflow-x-auto">
                    <p class="font-mono text-base text-center whitespace-nowrap leading-relaxed">
                        @foreach(str_split($maskedUrl) as $char)
                            @if($char === '_')
                                <span class="text-amber-400 font-bold border-b-2 border-amber-400 px-px">_</span>
                            @else
                                <span class="text-gray-200">{{ $char }}</span>
                            @endif
                        @endforeach
                    </p>
                </div>
                <p class="text-xs text-gray-400 text-center mt-2">
                    The <span class="text-amber-500 font-semibold">highlighted blanks</span> are the characters you need to enter, in order.
                </p>
            </div>

            {{-- Answer form --}}
            <form action="{{ route('bot-challenge.verify') }}" method="post">
                @csrf

                <div class="mb-5">
                    <label for="answer" class="block text-sm font-medium text-gray-700 mb-2 text-center">
                        Enter the 6 missing characters (left to right)
                    </label>
                    <input
                        type="text"
                        id="answer"
                        name="answer"
                        maxlength="6"
                        placeholder="______"
                        autocomplete="off"
                        autocorrect="off"
                        autocapitalize="off"
                        spellcheck="false"
                        value="{{ old('answer') }}"
                        class="block w-full max-w-xs mx-auto border-2 border-gray-300 rounded-lg px-4 py-3 text-2xl text-center font-mono tracking-[0.6em] bg-white focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200 @error('answer') border-red-400 @enderror"
                        required
                        autofocus>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition-colors">
                    Verify
                </button>

                <p class="text-xs text-gray-400 text-center mt-3">
                    Wrong challenge? <a href="{{ route('bot-challenge') }}" class="underline hover:text-gray-600">Reload for a new one</a>
                </p>
            </form>
        </div>

        {{-- About strip --}}
        <div class="bg-white rounded-xl shadow border border-gray-200 p-6 mb-6">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4 text-center">About This Platform</h3>
            <p class="text-sm text-gray-600 text-center mb-4">
                A private, secure marketplace for confidential transactions between buyers and vendors.
            </p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 text-center text-xs text-gray-500">
                <div class="border border-gray-100 rounded-lg p-3">
                    <div class="font-semibold text-gray-800 mb-0.5">Bitcoin</div>
                    BTC Payments
                </div>
                <div class="border border-gray-100 rounded-lg p-3">
                    <div class="font-semibold text-gray-800 mb-0.5">Monero</div>
                    XMR Privacy
                </div>
                <div class="border border-gray-100 rounded-lg p-3">
                    <div class="font-semibold text-gray-800 mb-0.5">Escrow</div>
                    Buyer Protection
                </div>
                <div class="border border-gray-100 rounded-lg p-3">
                    <div class="font-semibold text-gray-800 mb-0.5">PGP</div>
                    Encrypted Chat
                </div>
            </div>
        </div>

        {{-- Account links --}}
        <div class="flex justify-center gap-3 mb-6">
            <a href="{{ route('register') }}"
               class="px-5 py-2 border border-amber-600 text-amber-700 font-medium rounded-lg hover:bg-amber-50 transition-colors text-sm">
                Create Account
            </a>
            <a href="{{ route('login') }}"
               class="px-5 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-sm">
                Sign In
            </a>
        </div>

        <div class="text-center">
            <p class="text-xs text-gray-400">© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>

    </div>

</body>
</html>
