@extends('layouts.admin')

@section('title', 'Create Canary')

@section('page-heading')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-amber-900">Create Canary</h1>
            <p class="text-amber-700 mt-1">Publish a new PGP-signed warrant canary</p>
        </div>
        <a href="{{ route('admin.canaries.index') }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden border border-amber-200">
    <form action="{{ route('admin.canaries.store') }}" method="POST">
        @csrf
        
        <div class="p-6 space-y-6">
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                    Canary Message (PGP-Signed)
                </label>
                <textarea 
                    name="message" 
                    id="message" 
                    rows="20"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono text-sm @error('message') border-red-500 @enderror"
                    placeholder="-----BEGIN PGP SIGNED MESSAGE-----&#10;Hash: SHA512&#10;&#10;As of [DATE], [MARKET NAME] has NOT:&#10;- Received any National Security Letters&#10;- Received any gag orders&#10;- Been subject to any warrant canary gag orders&#10;...&#10;&#10;-----BEGIN PGP SIGNATURE-----&#10;...&#10;-----END PGP SIGNATURE-----">{{ old('message') }}</textarea>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">
                    Sign your message with your admin PGP key before pasting it here. Users will verify the signature 
                    using your public key from the Market Staff Keys page.
                </p>
            </div>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.canaries.index') }}" 
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-100">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                Publish Canary
            </button>
        </div>
    </form>
</div>

<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
    <h3 class="text-lg font-bold text-blue-900 mb-3">How to Create a Signed Canary</h3>
    <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
        <li>Write your canary message in a text editor (include date, statements about legal requests, etc.)</li>
        <li>Sign the message with your admin PGP private key using: <code class="bg-blue-100 px-2 py-1 rounded">gpg --clearsign message.txt</code></li>
        <li>Copy the entire signed message (including PGP headers and signature)</li>
        <li>Paste it into the textarea above and publish</li>
    </ol>
</div>
@endsection
