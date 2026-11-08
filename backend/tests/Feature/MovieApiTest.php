<?php

namespace Tests\Feature;

use Tests\TestCase;
use Database\Seeders\MovieSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class MovieApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_movies_endpoint_returns_all_movies(): void
    {
        $this->seed(MovieSeeder::class);

        $response = $this->getJson('/api/movies');

        $response->assertStatus(200)
                 ->assertJsonCount(20, 'movies');
    }

    public function test_movies_can_be_searched_by_title(): void
    {
        $this->seed(MovieSeeder::class);

        $response = $this->getJson('/api/movies?search=Inception');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'movies')
                 ->assertJsonPath('movies.0.title', 'Inception');
    }

    public function test_movies_can_be_filtered_by_genre(): void
    {
        $this->seed(MovieSeeder::class);

        $response = $this->getJson('/api/movies?genre=Sci-Fi');

        $response->assertStatus(200)
                 ->assertJsonCount(7, 'movies');
    }

    public function test_movie_detail_can_be_retrieved_by_id(): void
    {
        $this->seed(MovieSeeder::class);

        $response = $this->getJson('/api/movies/1');

        $response->assertStatus(200)
                 ->assertJsonPath('movie.title', 'Inception');
    }

    public function test_movie_detail_returns_404_when_not_found(): void
    {
        $this->seed(MovieSeeder::class);

        $response = $this->getJson('/api/movies/999');

        $response->assertStatus(404)
                 ->assertJsonPath('message', 'Movie not found.');
    }

    public function test_user_can_login_and_receive_jwt_token(): void
    {
        User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'testuser@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'token',
                     'user' => ['id', 'name', 'username', 'email'],
                ]);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'testuser@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Invalid email or password.',
                 ]);
    }

    public function test_user_can_register_successfully(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'user' => ['id', 'name', 'username', 'email'],
                ])
                ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_user_cannot_register_with_duplicate_email(): void
    {
        User::create([
            'name' => 'Existing User',
            'username' => 'existinguser',
            'email' => 'existing@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/register', [
            'username' => 'anotheruser',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }

    public function test_authenticated_user_can_view_watchlist(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $token = auth('api')->login($user);

        $response = $this->withToken($token)
                         ->getJson('/api/watchlist');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'watchlists',
                 ]);
    }

    public function test_authenticated_user_can_add_movie_to_watchlist(): void
    {
        $this->seed(MovieSeeder::class);

        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $token = auth('api')->login($user);

        $response = $this->withToken($token)
                         ->postJson('/api/watchlist', [
                             'movieId' => 1,
                         ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('watchlists', [
            'user_id' => $user->id,
            'movie_id' => 1,
        ]);
    }

    public function test_user_cannot_add_same_movie_twice_to_watchlist(): void
    {
        $this->seed(MovieSeeder::class);

        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $token = auth('api')->login($user);

        $this->withToken($token)->postJson('/api/watchlist', [
            'movieId' => 1,
        ])->assertStatus(201);

        $response = $this->withToken($token)->postJson('/api/watchlist', [
            'movieId' => 1,
        ]);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => 'Movie is already in the watchlist.',
                 ]);
    }

    public function test_authenticated_user_can_remove_movie_from_watchlist(): void
    {
        $this->seed(MovieSeeder::class);

        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $token = auth('api')->login($user);

        $this->withToken($token)->postJson('/api/watchlist', [
            'movieId' => 1,
        ])->assertStatus(201);

        $response = $this->withToken($token)
                         ->deleteJson('/api/watchlist/1');

        $response->assertStatus(200);

        $this->assertDatabaseMissing('watchlists', [
            'user_id' => $user->id,
            'movie_id' => 1,
        ]);
    }

    public function test_unauthenticated_user_cannot_view_watchlist(): void
    {
        $response = $this->getJson('/api/watchlist');

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }

    public function test_authenticated_user_can_mark_movie_as_watched(): void
    {
        $this->seed(MovieSeeder::class);

        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $token = auth('api')->login($user);

        $response = $this->withToken($token)
                         ->postJson('/api/watched', [
                             'movieId' => 1,
                             'rating' => 5,
                             'review' => 'A great movie!',
                         ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'movie_id' => 1,
            'rating' => 5,
            'review' => 'A great movie!',
        ]);
    }

    public function test_authenticated_user_can_view_watched_movies(): void
    {
        $this->seed(MovieSeeder::class);

        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $token = auth('api')->login($user);

        $this->withToken($token)->postJson('/api/watched', [
            'movieId' => 1,
            'rating' => 5,
            'review' => 'A great movie!',
        ]);

        $response = $this->withToken($token)
                         ->getJson('/api/watched');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'watched');
    }

    public function test_watched_movie_rejects_invalid_rating(): void
    {
        $this->seed(MovieSeeder::class);

        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $token = auth('api')->login($user);

        $response = $this->withToken($token)
                         ->postJson('/api/watched', [
                             'movieId' => 1,
                             'rating' => 6,
                             'review' => 'A great movie!',
                        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('rating');
    }

    public function test_unauthenticated_user_cannot_view_watched_movies(): void
    {
        $response = $this->getJson('/api/watched');

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }

    public function test_authenticated_user_can_view_statistics(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $token = auth('api')->login($user);

        $response = $this->withToken($token)
                         ->getJson('/api/statistics');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'watchlist_count',
                     'watched_count',
                     'average_rating',
                    'favorite_genre',
                ]);
    }

    public function test_unauthenticated_user_cannot_view_statistics(): void
    {
        $response = $this->getJson('/api/statistics');

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }

    public function test_gemini_recommends_a_movie_from_local_catalog(): void
    {
        $this->seed(MovieSeeder::class);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'movie_id' => 1,
                                        'reasoning' => 'Inception has a complex science fiction storyline.',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/movies/recommend', [
            'prompt' => 'I like complex science fiction movies.',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'movie_id' => 1,
                     'reasoning' => 'Inception has a complex science fiction storyline.',
                ]);
    }

    public function test_gemini_rejects_empty_prompt(): void
    {
        $response = $this->postJson('/api/movies/recommend', [
            'prompt' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('prompt');
    }

    public function test_gemini_rejects_movie_not_in_local_catalog(): void
    {
        $this->seed(MovieSeeder::class);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'movie_id' => 999,
                                        'reasoning' => 'This movie matches your preference.',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/movies/recommend', [
            'prompt' => 'Recommend a science fiction movie.',
        ]);

        $response->assertStatus(502)
                 ->assertJson([
                     'message' => 'Gemini recommended a movie that does not exist in the catalog.',
                 ]);
    }
}