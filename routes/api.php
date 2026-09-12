<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\MovieController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:movies')->group(function () {
    Route::prefix('movies')->group(function () {
        Route::get('/popular', [MovieController::class, 'popular']);
        Route::get('/search', [MovieController::class, 'search']);
        Route::get('/{id}', [MovieController::class, 'show'])->where('id', '[0-9]+');
    });

    Route::get('/genres', [GenreController::class, 'index']);
});

Route::prefix('auth')->group(function () {
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:api')->prefix('favorites')->group(function () {
    Route::get('/', [FavoriteController::class, 'index']);
    Route::post('/', [FavoriteController::class, 'store']);
    Route::delete('/{tmdbMovieId}', [FavoriteController::class, 'destroy'])->where('tmdbMovieId', '[0-9]+');
});
