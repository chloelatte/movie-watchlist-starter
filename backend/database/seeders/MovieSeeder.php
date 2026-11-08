<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = base_path('data/movies.json');

        $movies = json_decode(file_get_contents($file), true);

        foreach ($movies as $movie) {
            DB::table('movies')->insert([
                'id' => $movie['id'],
                'title' => $movie['title'],
                'year' => $movie['year'],
                'director' => $movie['director'],
                'genres' => json_encode($movie['genres']),
                'rating' => $movie['rating'],
                'duration' => $movie['duration'],
                'synopsis' => $movie['synopsis'],
                'poster_url' => $movie['poster_url'],
                'cast' => json_encode($movie['cast']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
