<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use App\Models\Review;

class StatisticsController extends Controller
{
    public function index()
    {
        $userId = auth('api')->id();

        $watchlistCount = Watchlist::where('user_id', $userId)->count();
        $watchedMovies = Review::with('movie')->where('user_id', $userId)->get()->unique('movie_id')->filter(fn ($review) => $review->movie !== null);
        $watchedCount = $watchedMovies->count();

        $averageRating = Review::where('user_id', $userId)->avg('rating');
        $genreCounts = [];

        foreach ($watchedMovies as $watched) {
            foreach ($watched->movie->genres ?? [] as $genre) {
                $genreCounts[$genre] = ($genreCounts[$genre] ?? 0) + 1;
            }
        }

        arsort($genreCounts);

        $favoriteGenre = array_key_first($genreCounts);

        return response()->json([
            'watchlist_count' => $watchlistCount,
            'watched_count' => $watchedCount,
            'average_rating' => $averageRating !== null ? round((float) $averageRating, 1) : null,
            'favorite_genre' => $favoriteGenre,
        ], 200);
    }
}
