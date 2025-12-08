<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\ListingMedia;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all vendors
        $vendors = User::whereHas('roles', function ($query) {
            $query->where('name', 'vendor');
        })->get();

        if ($vendors->isEmpty()) {
            $this->command->warn('No vendors found. Please run UserSeeder first.');
            return;
        }

        // Create 20 listings distributed among vendors
        $listingsPerVendor = (int) ceil(20 / $vendors->count());

        foreach ($vendors as $vendor) {
            for ($i = 0; $i < $listingsPerVendor; $i++) {
                $listing = Listing::factory()->create([
                    'user_id' => $vendor->id,
                ]);

                // Add 1-3 base64 images to each listing
                $imageCount = rand(1, 3);
                for ($j = 0; $j < $imageCount; $j++) {
                    // Generate a simple colored rectangle as base64 (placeholder)
                    $base64Image = $this->generatePlaceholderImage($listing->title, $j);
                    
                    ListingMedia::create([
                        'listing_id' => $listing->id,
                        'content' => $base64Image,
                        'type' => 'image/png',
                        'order' => $j,
                    ]);
                }
            }
        }

        $this->command->info('Created 20 listings with placeholder images.');
    }

    /**
     * Generate a simple placeholder base64 image.
     */
    private function generatePlaceholderImage(string $title, int $index): string
    {
        // Create a simple 400x300 image with GD
        $width = 400;
        $height = 300;
        $image = imagecreatetruecolor($width, $height);

        // Random background color
        $colors = [
            [255, 200, 100], // Orange
            [100, 150, 255], // Blue
            [150, 255, 150], // Green
            [255, 150, 200], // Pink
            [200, 150, 255], // Purple
        ];
        
        $colorIndex = $index % count($colors);
        $bgColor = imagecolorallocate($image, $colors[$colorIndex][0], $colors[$colorIndex][1], $colors[$colorIndex][2]);
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

        // Add text
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $text = substr($title, 0, 30);
        imagestring($image, 5, 10, $height / 2 - 10, $text, $textColor);
        imagestring($image, 3, 10, $height / 2 + 10, "Image " . ($index + 1), $textColor);

        // Convert to base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return base64_encode($imageData);
    }
}
