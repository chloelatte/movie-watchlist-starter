<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class GeminiController extends Controller
{
    public function recommend(Request $request)
    {
        // Validate user prompt
        $validated = $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        // Get all available movies from local database
        $movies = Movie::all();

        // Prepare movie catalog for Gemini
        $catalog = $movies->map(function ($movie) {
            return [
                'id' => $movie->id,
                'title' => $movie->title,
                'year' => $movie->year,
                'genres' => $movie->genres,
                'director' => $movie->director,
                'synopsis' => $movie->synopsis,
            ];
        });

        // Create prompt for Gemini
        $prompt = "
            You are a movie recommendation assistant.

            Recommend exactly one movie from the catalog below.
            You MUST select a movie that exists in this catalog.
            Never invent a movie title or movie ID.

            Movie catalog:
            " . $catalog->toJson() . "

            User preference:
            " . $validated['prompt'] . "

            Return your answer as valid JSON with exactly these keys:
            {
                \"movie_id\": 1,
                \"reasoning\": \"Explain why this movie matches the user's preference.\"
            }
        ";

        // Send request to Gemini
        try {
            $response = Http::timeout(60)->withHeaders([
                'x-goog-api-key' => config('services.gemini.api_key'),
            ])
            ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent',
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ],
            ]);
        } catch (ConnectionException $e) {
            return response()->json([
                'message' => 'Unable to connect to Gemini API.'
            ], 502);
        }

        // Handle Gemini API errors
        if ($response->failed()) {
            return response()->json([
                'message' => 'Failed to generate movie recommendation.',
            ], 502);
        }

        // Extract Gemini response
        $result = $response->json('candidates.0.content.parts.0.text');

        // Check wheter Gemini returned a response
        if (!is_string($result) || trim($result) === '') {
            return response()->json([
                'message' => 'Gemini returned an empty response.',
            ], 502);
        }

        //Convert Gemini JSON text into PHP array
        $recommendation = json_decode($result, true);

        //Validate Gemini response structure
        if(
            !is_array($recommendation) ||
            !isset($recommendation['movie_id']) ||
            !is_int($recommendation['movie_id']) ||
            !isset($recommendation['reasoning']) ||
            !is_string($recommendation['reasoning']) ||
            trim($recommendation['reasoning']) === ''
        ) {
            return response()->json([
                'message' => 'Gemini returned an invalid recommendation.',
            ], 502);
        }
        //Ensure the recommended movie belongs to the provided catalog
        $movie = $movies->firstWhere('id', $recommendation['movie_id']);
        if (!$movie) {
            return response()->json([
                'message' => 'Gemini recommended a movie that does not exist in the catalog.',
            ], 502);
        }

        // Return movie data and recommendation reasoning
        return response()->json([
            'movie_id' => $movie->id,
            'reasoning' => $recommendation['reasoning'],
        ], 200);
    }
}