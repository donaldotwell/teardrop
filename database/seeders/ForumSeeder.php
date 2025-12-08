<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ForumPost;
use App\Models\ForumComment;

class ForumSeeder extends Seeder
{
    public function run()
    {
        // Create sample forum posts
        $users = User::where('status', 'active')->take(5)->get();

        foreach ($users as $user) {
            // Create 2-3 posts per user
            for ($i = 0; $i < rand(2, 3); $i++) {
                $post = ForumPost::create([
                    'user_id' => $user->id,
                    'title' => fake()->sentence(rand(4, 8)),
                    'body' => fake()->paragraphs(rand(3, 6), true),
                    'last_activity_at' => now()->subDays(rand(0, 30)),
                    'views_count' => rand(10, 500),
                ]);

                // Add comments to posts
                $commenters = User::where('status', 'active')->inRandomOrder()->take(rand(2, 5))->get();
                foreach ($commenters as $commenter) {
                    ForumComment::create([
                        'user_id' => $commenter->id,
                        'forum_post_id' => $post->id,
                        'body' => fake()->paragraphs(rand(1, 3), true),
                    ]);
                }
            }
        }
    }
}
