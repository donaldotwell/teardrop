@extends($layout ?? 'layouts.app')

@section('title', 'Site Rules & Guidelines')

@section('page-heading')
    <h1 class="text-3xl font-bold text-gray-900">Site Rules & Guidelines</h1>
    <p class="text-gray-600 mt-1">Community standards and marketplace policies</p>
@endsection

@section('content')
<div class="space-y-6">
    {{-- General Rules --}}
    <div class="bg-white shadow rounded-lg overflow-hidden border border-red-200">
        <div class="px-6 py-4 bg-red-50 border-b border-red-200">
            <h2 class="text-2xl font-bold text-red-900">General Rules</h2>
            <p class="text-sm text-red-700 mt-1">All users must comply with these rules</p>
        </div>
        
        <div class="p-6">
            <ol class="list-decimal list-inside space-y-4 text-gray-800">
                <li class="leading-relaxed">
                    <strong>No Illegal Content:</strong> Do not post, sell, or request items prohibited by the marketplace rules including but not limited to: weapons, stolen data, hitman services, child exploitation material, or fentanyl/carfentanil.
                </li>
                <li class="leading-relaxed">
                    <strong>Respect Others:</strong> Treat all users with respect. No harassment, threats, doxxing, or discriminatory behavior will be tolerated.
                </li>
                <li class="leading-relaxed">
                    <strong>No Scamming:</strong> All vendors must deliver as described. Buyers must finalize orders when received. Scamming will result in immediate permanent ban.
                </li>
                <li class="leading-relaxed">
                    <strong>One Account Per Person:</strong> Multi-accounting is strictly prohibited and will result in all accounts being banned.
                </li>
                <li class="leading-relaxed">
                    <strong>No External Links:</strong> Do not share external market links, phishing sites, or attempt to conduct business off-platform.
                </li>
                <li class="leading-relaxed">
                    <strong>Proper OpSec:</strong> Use PGP encryption for sensitive communications. Never share personal information. Enable 2FA on your account.
                </li>
                <li class="leading-relaxed">
                    <strong>No Spam:</strong> Do not spam the forums, messaging system, or create fake reviews. Quality over quantity.
                </li>
                <li class="leading-relaxed">
                    <strong>Dispute Resolution:</strong> Use the built-in dispute system for order issues. Do not threaten vendors or buyers publicly.
                </li>
            </ol>
        </div>
    </div>

    {{-- Vendor Rules --}}
    <div class="bg-white shadow rounded-lg overflow-hidden border border-amber-200">
        <div class="px-6 py-4 bg-amber-50 border-b border-amber-200">
            <h2 class="text-2xl font-bold text-amber-900">Vendor Rules</h2>
            <p class="text-sm text-amber-700 mt-1">Additional requirements for vendors</p>
        </div>
        
        <div class="p-6">
            <ol class="list-decimal list-inside space-y-4 text-gray-800">
                <li class="leading-relaxed">
                    <strong>Vendor Bond:</strong> A non-refundable vendor bond of $1000 USD (in BTC or XMR) is required to become a vendor.
                </li>
                <li class="leading-relaxed">
                    <strong>Accurate Listings:</strong> All listings must accurately describe products, quantities, and shipping times. Photos must be authentic.
                </li>
                <li class="leading-relaxed">
                    <strong>Timely Shipping:</strong> Ship orders within stated timeframe. Update tracking when available. Communicate delays immediately.
                </li>
                <li class="leading-relaxed">
                    <strong>Quality Control:</strong> Test your products. Package securely and discreetly. Use proper stealth methods.
                </li>
                <li class="leading-relaxed">
                    <strong>Customer Service:</strong> Respond to messages within 24 hours. Resolve issues professionally. Accept legitimate refund requests.
                </li>
                <li class="leading-relaxed">
                    <strong>No Selective Scamming:</strong> Intentionally not shipping to certain buyers while shipping to others is considered selective scamming and will result in ban.
                </li>
                <li class="leading-relaxed">
                    <strong>PGP Mandatory:</strong> Vendors must have a valid PGP key and use it for all sensitive customer communications.
                </li>
                <li class="leading-relaxed">
                    <strong>Vacation Mode:</strong> Enable vacation mode if unable to process orders. Do not accept orders you cannot fulfill.
                </li>
            </ol>
        </div>
    </div>

    {{-- Buyer Rules --}}
    <div class="bg-white shadow rounded-lg overflow-hidden border border-blue-200">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-200">
            <h2 class="text-2xl font-bold text-blue-900">Buyer Rules</h2>
            <p class="text-sm text-blue-700 mt-1">Guidelines for safe purchasing</p>
        </div>
        
        <div class="p-6">
            <ol class="list-decimal list-inside space-y-4 text-gray-800">
                <li class="leading-relaxed">
                    <strong>Research Vendors:</strong> Check vendor reviews, ratings, and history before purchasing. New vendors carry higher risk.
                </li>
                <li class="leading-relaxed">
                    <strong>Use Escrow:</strong> Always use escrow for first-time purchases. Only use FE (Finalize Early) for trusted vendors with your consent.
                </li>
                <li class="leading-relaxed">
                    <strong>Encrypt Addresses:</strong> Always encrypt your shipping address with the vendor's PGP key. Never send unencrypted addresses.
                </li>
                <li class="leading-relaxed">
                    <strong>Finalize Promptly:</strong> Once you receive your order as described, finalize within 48 hours. Vendors need to be paid.
                </li>
                <li class="leading-relaxed">
                    <strong>Honest Reviews:</strong> Leave honest, detailed reviews. Help the community make informed decisions.
                </li>
                <li class="leading-relaxed">
                    <strong>No Blackmail:</strong> Do not threaten negative reviews to extort free products or refunds. This will result in ban.
                </li>
                <li class="leading-relaxed">
                    <strong>Communicate:</strong> If there's an issue, message the vendor first. Give them a chance to resolve it before opening a dispute.
                </li>
                <li class="leading-relaxed">
                    <strong>Responsible Usage:</strong> This marketplace does not condone drug abuse. Practice harm reduction. Know your limits.
                </li>
            </ol>
        </div>
    </div>

    {{-- Consequences --}}
    <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Consequences of Rule Violations</h2>
        </div>
        
        <div class="p-6">
            <div class="space-y-4 text-gray-800">
                <div class="flex items-start gap-3">
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-bold rounded">WARNING</span>
                    <p>Minor infractions (spam, heated arguments, minor listing errors) may result in a warning.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-bold rounded">SUSPENSION</span>
                    <p>Repeated violations or moderate infractions may result in temporary account suspension (7-30 days).</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-bold rounded">PERMANENT BAN</span>
                    <p>Serious violations (scamming, doxxing, prohibited items, multi-accounting) result in permanent ban with no appeal.</p>
                </div>
                <div class="mt-6 p-4 bg-amber-50 border border-amber-300 rounded">
                    <p class="text-sm text-amber-900">
                        <strong>Note:</strong> All disputes are reviewed by staff. Evidence must be provided. Staff decisions are final.
                        If you believe you were banned in error, you may submit a ticket from a new account with evidence.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Forum Rules --}}
    <div class="bg-white shadow rounded-lg overflow-hidden border border-purple-200">
        <div class="px-6 py-4 bg-purple-50 border-b border-purple-200">
            <h2 class="text-2xl font-bold text-purple-900">Forum Rules</h2>
        </div>
        
        <div class="p-6">
            <ul class="list-disc list-inside space-y-3 text-gray-800">
                <li>Stay on topic within each forum section</li>
                <li>No advertising or vendor shilling in the forums</li>
                <li>No posting of tracking numbers or order details publicly</li>
                <li>No discussion of violence or illegal activities beyond marketplace goods</li>
                <li>Use the search function before creating duplicate threads</li>
                <li>Be constructive with criticism - personal attacks will be removed</li>
                <li>No posting of vendor or buyer personal information</li>
            </ul>
        </div>
    </div>
</div>
@endsection
