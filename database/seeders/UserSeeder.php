<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure roles exist
        $adminRole = Role::where('name', 'admin')->first();
        $moderatorRole = Role::where('name', 'moderator')->first();
        $vendorRole = Role::where('name', 'vendor')->first();
        $userRole = Role::where('name', 'user')->first();

        // Create Admin User
        $admin = User::firstOrCreate(
            ['username_pri' => 'admin'],
            [
                'username_pub' => 'admin',
                'pin' => Hash::make('123456'),
                'password' => Hash::make('password'),
                'passphrase_1' => 'admin passphrase one',
                'passphrase_2' => 'admin passphrase two',
                'trust_level' => 5,
                'vendor_level' => 0,
                'last_login_at' => now(),
                'last_seen_at' => now(),
                'status' => 'active',
                'pgp_pub_key' => null,
            ]
        );
        if (!$admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole);
        }

        // Create 3 Moderators
        for ($i = 1; $i <= 3; $i++) {
            $moderator = User::firstOrCreate(
                ['username_pri' => "moderator{$i}"],
                [
                    'username_pub' => "Moderator{$i}",
                    'pin' => Hash::make('123456'),
                    'password' => Hash::make('password'),
                    'passphrase_1' => "moderator {$i} passphrase one",
                    'passphrase_2' => "moderator {$i} passphrase two",
                    'trust_level' => 3,
                    'vendor_level' => 0,
                    'last_login_at' => now()->subDays(rand(1, 7)),
                    'last_seen_at' => now()->subHours(rand(1, 24)),
                    'status' => 'active',
                    'pgp_pub_key' => null,
                ]
            );
            if (!$moderator->roles()->where('role_id', $moderatorRole->id)->exists()) {
                $moderator->roles()->attach($moderatorRole);
            }
        }

        // Create 3 Vendors
        for ($i = 1; $i <= 3; $i++) {
            $vendor = User::firstOrCreate(
                ['username_pri' => "vendor{$i}"],
                [
                    'username_pub' => "Vendor{$i}",
                    'pin' => Hash::make('123456'),
                    'password' => Hash::make('password'),
                    'passphrase_1' => "vendor {$i} passphrase one",
                    'passphrase_2' => "vendor {$i} passphrase two",
                    'trust_level' => 2,
                    'vendor_level' => rand(1, 3),
                    'vendor_since' => now()->subMonths(rand(1, 12)),
                    'last_login_at' => now()->subDays(rand(1, 3)),
                    'last_seen_at' => now()->subMinutes(rand(5, 60)),
                    'status' => 'active',
                    'pgp_pub_key' => null,
                ]
            );
            if (!$vendor->roles()->where('role_id', $vendorRole->id)->exists()) {
                $vendor->roles()->attach($vendorRole);
            }
        }

        // Create 3 Regular Users
        for ($i = 1; $i <= 3; $i++) {
            $user = User::firstOrCreate(
                ['username_pri' => "user{$i}"],
                [
                    'username_pub' => "User{$i}",
                    'pin' => Hash::make('123456'),
                    'password' => Hash::make('password'),
                    'passphrase_1' => "user {$i} passphrase one",
                    'passphrase_2' => "user {$i} passphrase two",
                    'trust_level' => 1,
                    'vendor_level' => 0,
                    'last_login_at' => now()->subDays(rand(1, 5)),
                    'last_seen_at' => now()->subHours(rand(1, 12)),
                    'status' => 'active',
                    'pgp_pub_key' => null,
                ]
            );
            if (!$user->roles()->where('role_id', $userRole->id)->exists()) {
                $user->roles()->attach($userRole);
            }
        }

        $this->command->info('Created 1 admin, 3 moderators, 3 vendors, and 3 users.');
    }
}
