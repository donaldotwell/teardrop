@extends('layouts.autoshop')

@section('page-title', 'FSAID Receipt — Autoshop')
@section('page-heading', 'FSAID Purchase Receipt')

@section('breadcrumbs')
<a href="{{ route('autoshop.fsaid.my-purchases') }}" class="hover:text-gray-900">My FSAID Purchases</a>
<span class="text-gray-400 mx-1">/</span>
<span>Receipt</span>
@endsection

@section('content')

    {{-- Purchase summary --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <div class="text-xs text-gray-500 mb-0.5">Base</div>
                <div class="font-medium">{{ $purchase->base?->name ?? 'Multiple Bases' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500 mb-0.5">Vendor</div>
                <div class="font-medium">{{ $purchase->vendor->username_pub }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500 mb-0.5">Records</div>
                <div class="font-bold text-lg text-gray-900">{{ $purchase->record_count }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500 mb-0.5">Total Paid</div>
                <div class="font-bold text-amber-700">
                    {{ number_format($purchase->total_crypto, $purchase->currency === 'btc' ? 8 : 12) }}
                    {{ strtoupper($purchase->currency) }}
                </div>
                <div class="text-xs text-gray-400">${{ number_format($purchase->total_usd, 2) }} USD</div>
            </div>
        </div>
        <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-400">
            Purchased {{ $purchase->created_at->format('M d, Y H:i') }}
        </div>
    </div>

    {{-- Full records --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-3 border-b border-teal-200 bg-teal-50">
            <h2 class="text-sm font-semibold text-teal-900">Purchased Records — Full Details</h2>
        </div>

        @foreach($purchase->records as $i => $r)
        <div class="{{ $i > 0 ? 'border-t border-gray-200' : '' }}">
            <div class="px-5 py-2 bg-gray-100 border-b border-gray-200">
                <span class="text-xs font-semibold text-gray-600">#{{ $i + 1 }} — {{ $r->first_name }} {{ $r->last_name }}</span>
            </div>
            <div class="px-5 py-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-4 text-xs">

                    {{-- Identity --}}
                    <div>
                        <div class="text-gray-400 font-semibold uppercase tracking-wide text-xs mb-2">Identity</div>
                        <div class="space-y-1.5">
                            <div><span class="text-gray-500">Name:</span> <span class="font-medium text-gray-900">{{ $r->first_name }} {{ $r->last_name }}</span></div>
                            <div><span class="text-gray-500">DOB:</span> <span class="font-mono text-gray-800">{{ $r->dob ? explode(' ', trim($r->dob))[0] : '—' }}</span></div>
                            <div><span class="text-gray-500">SSN:</span> <span class="font-mono font-semibold text-red-700">{{ $r->ssn ?? '—' }}</span></div>
                        </div>
                    </div>

                    {{-- Address --}}
                    <div>
                        <div class="text-gray-400 font-semibold uppercase tracking-wide text-xs mb-2">Address</div>
                        <div class="space-y-1.5">
                            <div><span class="text-gray-500">Address:</span> <span class="text-gray-800">{{ $r->address ?? '—' }}</span></div>
                            <div><span class="text-gray-500">City:</span> <span class="text-gray-800">{{ $r->city ?? '—' }}</span></div>
                            <div><span class="text-gray-500">State:</span> <span class="text-gray-800">{{ $r->state ?? '—' }}</span></div>
                            <div><span class="text-gray-500">ZIP:</span> <span class="font-mono text-gray-800">{{ $r->zip ?? '—' }}</span></div>
                        </div>
                    </div>

                    {{-- Email / Account --}}
                    <div>
                        <div class="text-gray-400 font-semibold uppercase tracking-wide text-xs mb-2">Email Account</div>
                        <div class="space-y-1.5">
                            <div><span class="text-gray-500">Email:</span> <span class="font-mono text-gray-800 break-all">{{ $r->email }}</span></div>
                            <div><span class="text-gray-500">Email Pass:</span> <span class="font-mono font-semibold text-gray-900 break-all">{{ $r->email_pass ?? '—' }}</span></div>
                        </div>
                    </div>

                    {{-- FSAID Portal --}}
                    <div>
                        <div class="text-gray-400 font-semibold uppercase tracking-wide text-xs mb-2">FSAID Portal</div>
                        <div class="space-y-1.5">
                            <div><span class="text-gray-500">FA Username:</span> <span class="font-mono text-gray-800">{{ $r->fa_uname ?? '—' }}</span></div>
                            <div><span class="text-gray-500">FA Password:</span> <span class="font-mono font-semibold text-gray-900">{{ $r->fa_pass ?? '—' }}</span></div>
                            <div><span class="text-gray-500">2FA:</span> <span class="font-mono text-gray-800">{{ $r->two_fa ?? '—' }}</span></div>
                        </div>
                    </div>

                    {{-- Recovery --}}
                    <div>
                        <div class="text-gray-400 font-semibold uppercase tracking-wide text-xs mb-2">Recovery</div>
                        <div class="space-y-1.5">
                            <div><span class="text-gray-500">Backup Codes:</span> <span class="font-mono text-gray-800 break-all">{{ $r->backup_code ?? '—' }}</span></div>
                            <div><span class="text-gray-500">Security Q&amp;A:</span> <span class="text-gray-800 break-all">{{ $r->security_qa ?? '—' }}</span></div>
                        </div>
                    </div>

                    {{-- FAFSA / Enrollment --}}
                    <div>
                        <div class="text-gray-400 font-semibold uppercase tracking-wide text-xs mb-2">FAFSA Data</div>
                        <div class="space-y-1.5">
                            <div><span class="text-gray-500">Level:</span> <span class="text-gray-800">{{ $r->level ?? '—' }}</span></div>
                            <div><span class="text-gray-500">Enrollment:</span>
                                @if($r->enrollment)
                                    <span class="text-gray-800">{{ ucfirst($r->enrollment) }}</span>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </div>
                            <div><span class="text-gray-500">Programs:</span> <span class="text-gray-800 break-all">{{ $r->programs ?? '—' }}</span></div>
                            @if($r->enrollment_details)
                            <div><span class="text-gray-500">Details:</span> <span class="text-gray-800 break-all">{{ $r->enrollment_details }}</span></div>
                            @endif
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($r->description)
                    <div class="sm:col-span-2 lg:col-span-3">
                        <div class="text-gray-400 font-semibold uppercase tracking-wide text-xs mb-2">Description</div>
                        <p class="text-gray-700 break-all">{{ $r->description }}</p>
                    </div>
                    @endif

                </div>
            </div>
        </div>
        @endforeach
    </div>

@endsection
