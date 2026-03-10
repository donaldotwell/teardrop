<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fees - Platform Revenue
        AppSetting::set('vendor_conversion_usd', 1000, 'fees', 'decimal', 'Vendor conversion fee in USD');
        AppSetting::set('order_completion_percentage', 3, 'fees', 'decimal', 'Admin fee on order completion as percentage');
        AppSetting::set('featured_listing_usd', 10, 'fees', 'decimal', 'Cost to feature a listing in USD');

        // Admin Wallets
        AppSetting::set('admin_btc_wallet_name', 'admin', 'admin', 'string', 'Bitcoin wallet name for admin (receives fees)');
        AppSetting::set('admin_xmr_wallet_name', 'admin_xmr', 'admin', 'string', 'Monero wallet name for admin (receives fees)');

        // Early Finalization Settings
        AppSetting::set('early_finalization_vendor_level_required', 8, 'early_finalization', 'integer', 'Minimum vendor level to access early finalization');
        AppSetting::set('early_finalization_max_dispute_rate', 20, 'early_finalization', 'decimal', 'Maximum dispute rate percentage allowed for early finalization');
        AppSetting::set('early_finalization_require_pgp', 1, 'early_finalization', 'boolean', 'Require vendor to have PGP key for early finalization');

        // Dispute Settings
        AppSetting::set('dispute_window_days', 30, 'disputes', 'integer', 'Days to open a dispute after order completion');
        AppSetting::set('dispute_escalation_time_hours', 72, 'disputes', 'integer', 'Hours before dispute auto-escalates if not resolved');

        // Forum Settings
        AppSetting::set('forum_require_trust_level', 2, 'forum', 'integer', 'Minimum trust level to post in forum');
        AppSetting::set('forum_max_posts_per_day', 50, 'forum', 'integer', 'Maximum posts per user per day');

        // Ticket Settings
        AppSetting::set('ticket_response_sla_hours', 24, 'tickets', 'integer', 'Service level agreement - hours to first response');
        AppSetting::set('ticket_resolution_days', 7, 'tickets', 'integer', 'Target days to resolve support ticket');

        // Platform Settings
        AppSetting::set('allow_new_registrations', 1, 'platform', 'boolean', 'Allow new user registrations');
        AppSetting::set('max_listings_per_user', 20, 'platform', 'integer', 'Maximum active listings per user');
        AppSetting::set('max_images_per_listing', 3, 'platform', 'integer', 'Maximum images per listing');

        // Trust & Security
        AppSetting::set('new_user_trust_level', 1, 'security', 'integer', 'Initial trust level for new users');
        AppSetting::set('max_login_attempts', 5, 'security', 'integer', 'Maximum login attempts before lockout');
        AppSetting::set('session_lifetime_minutes', 1440, 'security', 'integer', 'Session lifetime in minutes (0 = session cookie)');
    }
}
