<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumPostFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(rand(4, 10)),
            'body' => $this->faker->paragraphs(rand(3, 8), true),
            'views_count' => $this->faker->numberBetween(0, 1000),
            'last_activity_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function pinned()
    {
        return $this->state(['is_pinned' => true]);
    }

    public function locked()
    {
        return $this->state(['is_locked' => true]);
    }
}
