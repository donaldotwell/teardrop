<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\ForumPost;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumCommentFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'forum_post_id' => ForumPost::factory(),
            'body' => $this->faker->paragraphs(rand(1, 3), true),
        ];
    }

    public function reply()
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_id' => \App\Models\ForumComment::factory(),
            ];
        });
    }
}
