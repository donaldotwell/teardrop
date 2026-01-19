<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class AggregateUserRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ratings:aggregate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate user ratings from reviews table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting user ratings aggregation...');

        // Get all users who have received reviews (via their listings)
        $usersWithReviews = DB::table('reviews')
            ->join('listings', 'reviews.listing_id', '=', 'listings.id')
            ->select('listings.user_id')
            ->groupBy('listings.user_id')
            ->pluck('user_id');

        if ($usersWithReviews->isEmpty()) {
            $this->info('No reviews found. Nothing to aggregate.');
            return 0;
        }

        $updatedCount = 0;

        foreach ($usersWithReviews as $userId) {
            // Calculate average rating for this user's listings
            // Using CAST to ensure decimal division works on both MySQL and PostgreSQL
            $averageRating = DB::table('reviews')
                ->join('listings', 'reviews.listing_id', '=', 'listings.id')
                ->where('listings.user_id', $userId)
                ->selectRaw('
                    AVG((CAST(rating_stealth AS DECIMAL) + CAST(rating_quality AS DECIMAL) + CAST(rating_delivery AS DECIMAL)) / 3.0) as avg_rating
                ')
                ->value('avg_rating');

            if ($averageRating !== null) {
                // Normalize to 5.00 scale (assuming ratings are 1-5)
                $normalizedRating = round((float) $averageRating, 2);

                // Update user's rating
                User::where('id', $userId)->update(['rating' => $normalizedRating]);

                $updatedCount++;
            }
        }

        $this->info("Successfully updated ratings for {$updatedCount} user(s).");
        return 0;
    }
}
