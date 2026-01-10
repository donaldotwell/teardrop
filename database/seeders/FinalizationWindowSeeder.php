<?php

namespace Database\Seeders;

use App\Models\FinalizationWindow;
use Illuminate\Database\Seeder;

class FinalizationWindowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $windows = [
            [
                'name' => 'Instant',
                'duration_minutes' => 0,
                'description' => 'No dispute window - immediate finalization with no recourse.',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => '10 Minutes',
                'duration_minutes' => 10,
                'description' => 'Very short dispute window for low-risk transactions.',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => '30 Minutes',
                'duration_minutes' => 30,
                'description' => 'Short dispute window for quick turnaround.',
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'name' => '1 Hour',
                'duration_minutes' => 60,
                'description' => 'One hour dispute window for fast verification.',
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'name' => '7 Days',
                'duration_minutes' => 10080,
                'description' => 'Standard dispute window - recommended for most transactions.',
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'name' => '3 Weeks',
                'duration_minutes' => 30240,
                'description' => 'Extended dispute window for high-value or international transactions.',
                'is_active' => true,
                'display_order' => 6,
            ],
        ];

        foreach ($windows as $window) {
            FinalizationWindow::updateOrCreate(
                ['name' => $window['name']],
                $window
            );
        }

        $this->command->info('Finalization windows seeded successfully.');
    }
}
