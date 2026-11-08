<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WatchlistController;
use App\Http\Controllers\Api\WatchedController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\GeminiController;
use App\Http\Controllers\Api\StatisticsController;

Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:register');

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{id}', [MovieController::class, 'show']);

Route::post('/movies/recommend', [GeminiController::class, 'recommend'])
    ->middleware('throttle:gemini');

Route::middleware('auth:api')->group(function () {
    Route::get('/watchlist', [WatchlistController::class, 'index']);
    Route::post('/watchlist', [WatchlistController::class, 'store']);
    Route::delete('/watchlist/{movieId}', [WatchlistController::class, 'destroy']);

    Route::get('/watched', [WatchedController::class, 'index']);
    Route::post('/watched', [WatchedController::class, 'store']);

    Route::get('/statistics', [StatisticsController::class, 'index']);
});