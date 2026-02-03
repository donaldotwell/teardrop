<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Step 1: Seed lookup/reference tables
            CountriesSeeder::class,
            ProductsSeeder::class,
            RolesSeeder::class,
            FinalizationWindowSeeder::class,
        ]);
        
        // Step 2: Seed users with roles (only in non-production environments)
        if (!app()->environment('production')) {
            $this->call(UserSeeder::class);
            $this->call(ListingSeeder::class);
            $this->command->info('Test users and listings seeded (non-production environment).');
        }
        
        $this->command->info('Database seeding completed successfully!');
    }
}
