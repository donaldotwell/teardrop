<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username_pri' => $this->faker->unique()->userName,
            'username_pub' => $this->faker->unique()->userName,
            'pin' => $this->faker->sha256,
            'password' => Hash::make('password'),
            'passphrase_1' => $this->faker->sentence,
            'passphrase_2' => $this->faker->sentence,
            'trust_level' => 1,
            'vendor_level' => 0,
            'last_login_at' => $this->faker->dateTime,
            'last_seen' => $this->faker->dateTime,
            'status' => 'active',
            'pgp_pub_key' => $this->faker->text,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
