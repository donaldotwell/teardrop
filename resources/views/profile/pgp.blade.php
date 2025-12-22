@extends('layouts.app')
@section('page-title', 'Setup PGP Key')

@section('breadcrumbs')
    <a href="{{ route('profile.show') }}" class="text-yellow-600 hover:text-yellow-700">Profile</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600">PGP Key Setup</span>
@endsection

@section('page-heading')
    PGP Public Key Setup
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white border border-gray-200 rounded-lg p-6">

            {{-- Information Banner --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-900 mb-2">About PGP Key Verification</h3>
                <div class="text-sm text-blue-800 space-y-2">
                    <p>To ensure you control the private key, we will encrypt a verification challenge with your public key.</p>
                    <p><strong>Process:</strong></p>
                    <ol class="list-decimal list-inside ml-4 space-y-1">
                        <li>Submit your PGP public key below</li>
                        <li>We will encrypt a random code with your public key</li>
                        <li>Decrypt the message using your private key</li>
                        <li>Submit the decrypted verification code</li>
                        <li>Your key will be saved after successful verification</li>
                    </ol>
                </div>
            </div>

            @if($hasExistingKey)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                    <h4 class="font-semibold text-amber-900 mb-1">Current PGP Key</h4>
                    <p class="text-sm text-amber-800">You already have a PGP key configured. Proceeding will replace it after verification.</p>
                    <div class="mt-3 p-3 bg-white rounded border border-amber-200">
                        <pre class="text-xs text-gray-700 overflow-x-auto">{{ Str::limit($user->pgp_pub_key, 200) }}</pre>
                    </div>
                </div>
            @endif

            {{-- PGP Key Form --}}
            <form action="{{ route('profile.pgp.initiate') }}" method="post" class="space-y-4">
                @csrf

                <div class="space-y-2">
                    <label for="pgp_pub_key" class="block text-sm font-medium text-gray-700">
                        PGP Public Key <span class="text-red-600">*</span>
                    </label>
                    <textarea name="pgp_pub_key"
                              id="pgp_pub_key"
                              rows="12"
                              class="block w-full px-3 py-2 border @error('pgp_pub_key') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 font-mono text-sm"
                              placeholder="-----BEGIN PGP PUBLIC KEY BLOCK-----&#10;&#10;-----END PGP PUBLIC KEY BLOCK-----"
                              required>{{ old('pgp_pub_key') }}</textarea>

                    <p class="text-xs text-gray-500">
                        Paste your complete PGP public key block including the BEGIN and END markers.
                    </p>

                    @error('pgp_pub_key')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-2 text-sm">Requirements:</h4>
                    <ul class="text-xs text-gray-700 space-y-1 list-disc list-inside">
                        <li>Must be a valid PGP/GPG public key</li>
                        <li>You must have access to the corresponding private key</li>
                        <li>Key will be used for encrypted communications on the marketplace</li>
                        <li>Verification code expires in 1 hour</li>
                        <li>Maximum 5 verification attempts</li>
                    </ul>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit"
                            class="px-6 py-2 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">
                        Start Verification
                    </button>
                    <a href="{{ route('profile.show') }}"
                       class="px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded hover:bg-gray-300">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Help Section --}}
        <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Need Help?</h3>

            <div class="space-y-4 text-sm text-gray-700">
                <div>
                    <h4 class="font-medium text-gray-900 mb-1">Generating a PGP Key Pair</h4>
                    <p class="text-xs mb-2">If you don't have a PGP key yet, you can generate one using:</p>
                    <ul class="list-disc list-inside text-xs space-y-1 ml-4">
                        <li><strong>GPG (Linux/Mac):</strong> <code class="bg-gray-100 px-1 py-0.5 rounded">gpg --full-generate-key</code></li>
                        <li><strong>Kleopatra (Windows):</strong> Download from gpg4win.org</li>
                        <li><strong>GPG Keychain (Mac):</strong> Download from gpgtools.org</li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900 mb-1">Exporting Your Public Key</h4>
                    <p class="text-xs mb-2">To export your public key:</p>
                    <code class="block bg-gray-100 px-3 py-2 rounded text-xs">
                        gpg --armor --export your-email@example.com
                    </code>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900 mb-1">Decrypting the Challenge</h4>
                    <p class="text-xs mb-2">Save the encrypted message to a file and decrypt:</p>
                    <code class="block bg-gray-100 px-3 py-2 rounded text-xs">
                        gpg --decrypt challenge.asc
                    </code>
                </div>
            </div>
        </div>
    </div>
@endsection
