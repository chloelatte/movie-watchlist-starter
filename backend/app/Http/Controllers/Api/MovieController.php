<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        // Validasi parameter pencarian dan filter
        $request->validate([
            'search' => 'sometimes|nullable|string|max:100',
            'genre' => 'sometimes|nullable|string|max:50',
            'random' => 'sometimes|nullable|boolean',
        ]);

        $query = Movie::query();

        if ($request->filled('genre')) {
            $query->whereJsonContains('genres', $request->genre);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->boolean('random')) {
            $query->inRandomOrder();
        }

        $movies = $query->get();

        return response()->json([
            'movies' => $movies,
        ], 200);
    }

    public function show($id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json([
                'message' => 'Movie not found.',
            ], 404);
        }

        return response()->json([
            'movie' => $movie,
        ], 200);
    }
}