<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class WatchedController extends Controller
{
    public function index()
    {
        $watched = Review::with('movie')
        ->where('user_id', auth('api')->id())->get();

        return response()->json([
            'watched' => $watched,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'movieId' => 'required|exists:movies,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string',
        ]);

        $watched = Review::create([
            'user_id' => auth('api')->id(),
            'movie_id' => $validated['movieId'],
            'rating' => $validated['rating'],
            'review' => $validated['review'],
        ]);

        return response()->json([
            'message' => 'Movie marked as watched successfully.',
            'watched' => $watched,
        ], 201);
    }
}
