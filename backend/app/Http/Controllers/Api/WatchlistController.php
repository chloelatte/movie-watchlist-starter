<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Watchlist;

class WatchlistController extends Controller
{
    public function index(Request $request)
    {
        $watchlists = Watchlist::with('movie')->where('user_id', auth('api')->id())->get();

        return response()->json([
            'watchlists' => $watchlists,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'movieId' => 'required|exists:movies,id',
        ]);

        $userId = auth('api')->id();

        $existingWatchlist = Watchlist::where('user_id', $userId)
            ->where('movie_id', $validated['movieId'])
            ->first();

        if ($existingWatchlist) {
            return response()->json([
                'message' => 'Movie is already in the watchlist.',
            ], 409);
        }

        $watchlist = Watchlist::create([
            'user_id' => $userId,
            'movie_id' => $validated['movieId'],
        ]);

        return response()->json([
            'message' => 'Movie added to watchlist successfully.',
            'watchlist' => $watchlist,
        ], 201);
    }

    public function destroy($movieId)
    {
        $watchlist = Watchlist::where('user_id', auth('api')->id())
        ->where('movie_id', $movieId)->first();

        if(!$watchlist) {
            return response()->json([
                'message' => 'Movie not found in watchlist.',
            ], 404);
        }

        $watchlist->delete();

        return response()->json([
            'message' => 'Movie removed from watchlist successfully.',
        ], 200);
    }
}
