<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get a random user with vendor role
        $vendor = User::whereHas('roles', function ($query) {
            $query->where('name', 'vendor');
        })->inRandomOrder()->first();

        // If no vendor exists, create one
        if (!$vendor) {
            $vendor = User::factory()->create();
            $vendorRole = \App\Models\Role::where('name', 'vendor')->first();
            if ($vendorRole) {
                $vendor->roles()->attach($vendorRole);
            }
        }

        return [
            'user_id' => $vendor->id,
            'product_id' => Product::inRandomOrder()->first()->id,
            'title' => $this->faker->sentence(6),
            'short_description' => $this->faker->sentence(10),
            'description' => $this->faker->paragraph(3),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'price_shipping' => $this->faker->randomFloat(2, 1, 100),
            'shipping_method' => $this->faker->randomElement(['pickup', 'delivery', 'shipping']),
            'payment_method' => $this->faker->randomElement(['escrow', 'direct']),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'quantity' => $this->faker->numberBetween(1, 100),
            'origin_country_id' => Country::inRandomOrder()->first()->id,
            'destination_country_id' => Country::inRandomOrder()->first()->id,
            'tags' => json_encode($this->faker->words(5)),
            'return_policy' => $this->faker->paragraph(1),
            'views' => $this->faker->numberBetween(0, 100),
            'is_featured' => $this->faker->boolean(30), // 30% chance of being featured
            'is_active' => true,
        ];
    }
}
