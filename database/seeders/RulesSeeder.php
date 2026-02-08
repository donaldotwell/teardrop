<?php

namespace Database\Seeders;

use App\Models\Rule;
use Illuminate\Database\Seeder;

class RulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // General Rules
            [
                'title' => 'No Illegal Content',
                'content' => 'Do not post, sell, or request items prohibited by the marketplace rules including but not limited to: weapons, stolen data, hitman services, child exploitation material, or fentanyl/carfentanil.',
                'category' => 'general',
                'order' => 1,
            ],
            [
                'title' => 'Respect Others',
                'content' => 'Treat all users with respect. No harassment, threats, doxxing, or discriminatory behavior will be tolerated.',
                'category' => 'general',
                'order' => 2,
            ],
            [
                'title' => 'No Scamming',
                'content' => 'All vendors must deliver as described. Buyers must finalize orders when received. Scamming will result in immediate permanent ban.',
                'category' => 'general',
                'order' => 3,
            ],
            [
                'title' => 'One Account Per Person',
                'content' => 'Multi-accounting is strictly prohibited and will result in all accounts being banned.',
                'category' => 'general',
                'order' => 4,
            ],
            [
                'title' => 'No External Links',
                'content' => 'Do not share external market links, phishing sites, or attempt to conduct business off-platform.',
                'category' => 'general',
                'order' => 5,
            ],
            [
                'title' => 'Proper OpSec',
                'content' => 'Use PGP encryption for sensitive communications. Never share personal information. Enable 2FA on your account.',
                'category' => 'general',
                'order' => 6,
            ],
            [
                'title' => 'No Spam',
                'content' => 'Do not spam the forums, messaging system, or create fake reviews. Quality over quantity.',
                'category' => 'general',
                'order' => 7,
            ],
            [
                'title' => 'Dispute Resolution',
                'content' => 'Use the built-in dispute system for order issues. Do not threaten vendors or buyers publicly.',
                'category' => 'general',
                'order' => 8,
            ],

            // Vendor Rules
            [
                'title' => 'Vendor Bond',
                'content' => 'A non-refundable vendor bond of $1000 USD (in BTC or XMR) is required to become a vendor.',
                'category' => 'vendor',
                'order' => 1,
            ],
            [
                'title' => 'Accurate Listings',
                'content' => 'All listings must accurately describe products, quantities, and shipping times. Photos must be authentic.',
                'category' => 'vendor',
                'order' => 2,
            ],
            [
                'title' => 'Timely Shipping',
                'content' => 'Ship orders within stated timeframe. Update tracking when available. Communicate delays immediately.',
                'category' => 'vendor',
                'order' => 3,
            ],
            [
                'title' => 'Quality Control',
                'content' => 'Test your products. Package securely and discreetly. Use proper stealth methods.',
                'category' => 'vendor',
                'order' => 4,
            ],
            [
                'title' => 'Customer Service',
                'content' => 'Respond to messages within 24 hours. Resolve issues professionally. Accept legitimate refund requests.',
                'category' => 'vendor',
                'order' => 5,
            ],
            [
                'title' => 'No Selective Scamming',
                'content' => 'Intentionally not shipping to certain buyers while shipping to others is considered selective scamming and will result in ban.',
                'category' => 'vendor',
                'order' => 6,
            ],
            [
                'title' => 'PGP Mandatory',
                'content' => 'Vendors must have a valid PGP key and use it for all sensitive customer communications.',
                'category' => 'vendor',
                'order' => 7,
            ],
            [
                'title' => 'Vacation Mode',
                'content' => 'Enable vacation mode if unable to process orders. Do not accept orders you cannot fulfill.',
                'category' => 'vendor',
                'order' => 8,
            ],

            // Buyer Rules
            [
                'title' => 'Research Vendors',
                'content' => 'Check vendor reviews, ratings, and history before purchasing. New vendors carry higher risk.',
                'category' => 'buyer',
                'order' => 1,
            ],
            [
                'title' => 'Use Escrow',
                'content' => 'Always use escrow for first-time purchases. Only use FE (Finalize Early) for trusted vendors with your consent.',
                'category' => 'buyer',
                'order' => 2,
            ],
            [
                'title' => 'Encrypt Addresses',
                'content' => 'Always encrypt your shipping address with the vendor\'s PGP key. Never send unencrypted addresses.',
                'category' => 'buyer',
                'order' => 3,
            ],
            [
                'title' => 'Finalize Promptly',
                'content' => 'Once you receive your order as described, finalize within 48 hours. Vendors need to be paid.',
                'category' => 'buyer',
                'order' => 4,
            ],
            [
                'title' => 'Honest Reviews',
                'content' => 'Leave honest, detailed reviews. Help the community make informed decisions.',
                'category' => 'buyer',
                'order' => 5,
            ],
            [
                'title' => 'No Blackmail',
                'content' => 'Do not threaten negative reviews to extort free products or refunds. This will result in ban.',
                'category' => 'buyer',
                'order' => 6,
            ],
            [
                'title' => 'Communicate',
                'content' => 'If there\'s an issue, message the vendor first. Give them a chance to resolve it before opening a dispute.',
                'category' => 'buyer',
                'order' => 7,
            ],
            [
                'title' => 'Responsible Usage',
                'content' => 'This marketplace does not condone drug abuse. Practice harm reduction. Know your limits.',
                'category' => 'buyer',
                'order' => 8,
            ],

            // Consequences
            [
                'title' => 'WARNING',
                'content' => 'Minor infractions (spam, heated arguments, minor listing errors) may result in a warning.',
                'category' => 'consequences',
                'order' => 1,
            ],
            [
                'title' => 'SUSPENSION',
                'content' => 'Repeated violations or moderate infractions may result in temporary account suspension (7-30 days).',
                'category' => 'consequences',
                'order' => 2,
            ],
            [
                'title' => 'PERMANENT BAN',
                'content' => 'Serious violations (scamming, doxxing, prohibited items, multi-accounting) result in permanent ban with no appeal.',
                'category' => 'consequences',
                'order' => 3,
            ],

            // Forum Rules
            [
                'title' => 'Stay on topic within each forum section',
                'content' => 'Keep discussions relevant to the forum category you are posting in.',
                'category' => 'forum',
                'order' => 1,
            ],
            [
                'title' => 'No advertising or vendor shilling in the forums',
                'content' => 'Forums are for community discussion, not for promoting your listings.',
                'category' => 'forum',
                'order' => 2,
            ],
            [
                'title' => 'No posting of tracking numbers or order details publicly',
                'content' => 'Keep order information private for security reasons.',
                'category' => 'forum',
                'order' => 3,
            ],
            [
                'title' => 'No discussion of violence or illegal activities beyond marketplace goods',
                'content' => 'Keep discussions focused on marketplace-related topics only.',
                'category' => 'forum',
                'order' => 4,
            ],
            [
                'title' => 'Use the search function before creating duplicate threads',
                'content' => 'Check if your topic has already been discussed before creating a new thread.',
                'category' => 'forum',
                'order' => 5,
            ],
            [
                'title' => 'Be constructive with criticism - personal attacks will be removed',
                'content' => 'Critique ideas, not people. Maintain civil discourse.',
                'category' => 'forum',
                'order' => 6,
            ],
            [
                'title' => 'No posting of vendor or buyer personal information',
                'content' => 'Doxxing of any kind will result in immediate permanent ban.',
                'category' => 'forum',
                'order' => 7,
            ],
        ];

        foreach ($rules as $rule) {
            Rule::create($rule);
        }
    }
}
