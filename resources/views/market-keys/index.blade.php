@extends('layouts.auth')

@section('title', 'Market Staff Keys')

@section('page-heading')
    <h1 class="text-3xl font-bold text-gray-900">Market Staff Keys</h1>
    <p class="text-gray-600 mt-1">Public PGP keys for verification and secure communication</p>
@endsection

@section('content')
<div class="space-y-8">
    {{-- Administrators Section --}}
    <div class="bg-white shadow rounded-lg overflow-hidden border border-amber-200">
        <div class="px-6 py-4 bg-amber-50 border-b border-amber-200">
            <h2 class="text-2xl font-bold text-amber-900">Administrators</h2>
            <p class="text-sm text-amber-700 mt-1">
                {{ $admins->count() }} {{ Str::plural('key', $admins->count()) }} available
            </p>
        </div>
        
        <div class="p-6">
            @forelse($admins as $admin)
                <div class="mb-8 last:mb-0 pb-8 last:pb-0 border-b last:border-b-0 border-gray-200">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $admin['username'] }}</h3>
                            <p class="text-sm text-gray-500">Member since {{ $admin['member_since'] }}</p>
                        </div>
                        <span class="px-3 py-1 bg-amber-100 text-amber-800 text-sm font-semibold rounded">
                            ADMIN
                        </span>
                    </div>
                    
                    @if($admin['fingerprint'])
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-gray-700 mb-1">PGP Fingerprint:</p>
                            <code class="block bg-gray-100 px-4 py-2 rounded text-xs font-mono text-gray-800 break-all">
                                {{ $admin['fingerprint'] }}
                            </code>
                        </div>
                    @endif
                    
                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-1">Public Key:</p>
                        <div class="bg-gray-50 border border-gray-200 rounded p-4">
                            <pre class="text-xs font-mono text-gray-800 whitespace-pre-wrap break-all">{{ $admin['pgp_key'] }}</pre>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-gray-500">No administrator keys available at this time.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Moderators Section --}}
    <div class="bg-white shadow rounded-lg overflow-hidden border border-amber-200">
        <div class="px-6 py-4 bg-amber-50 border-b border-amber-200">
            <h2 class="text-2xl font-bold text-amber-900">Moderators</h2>
            <p class="text-sm text-amber-700 mt-1">
                {{ $moderators->count() }} {{ Str::plural('key', $moderators->count()) }} available
            </p>
        </div>
        
        <div class="p-6">
            @forelse($moderators as $moderator)
                <div class="mb-8 last:mb-0 pb-8 last:pb-0 border-b last:border-b-0 border-gray-200">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $moderator['username'] }}</h3>
                            <p class="text-sm text-gray-500">Member since {{ $moderator['member_since'] }}</p>
                        </div>
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-sm font-semibold rounded">
                            MODERATOR
                        </span>
                    </div>
                    
                    @if($moderator['fingerprint'])
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-gray-700 mb-1">PGP Fingerprint:</p>
                            <code class="block bg-gray-100 px-4 py-2 rounded text-xs font-mono text-gray-800 break-all">
                                {{ $moderator['fingerprint'] }}
                            </code>
                        </div>
                    @endif
                    
                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-1">Public Key:</p>
                        <div class="bg-gray-50 border border-gray-200 rounded p-4">
                            <pre class="text-xs font-mono text-gray-800 whitespace-pre-wrap break-all">{{ $moderator['pgp_key'] }}</pre>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-gray-500">No moderator keys available at this time.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Information Section --}}
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-6">
        <h3 class="text-lg font-bold text-amber-900 mb-3">About This Directory</h3>
        <div class="text-sm text-amber-800 space-y-2">
            <p>
                This directory lists the public PGP keys of all active market staff members. 
                You can use these keys to verify staff identity and encrypt messages for secure communication.
            </p>
            <p>
                <strong>How to use:</strong> Copy the public key block and import it into your PGP software (GPG, Kleopatra, etc.). 
                Verify the fingerprint matches before encrypting sensitive information.
            </p>
            <p class="text-xs text-amber-700 mt-4">
                Note: Only active staff members with verified PGP keys are displayed. Keys are updated automatically.
            </p>
        </div>
    </div>
</div>
@endsection
