<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TMDBService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.tmdb.base_url');
        $this->token = config('services.tmdb.token');
    }

    protected function client()
    {
        return Http::withToken($this->token)
            ->baseUrl($this->baseUrl)
            ->acceptJson();
    }

    public function getPopularMovies(int $page = 1): array
    {
        $cacheKey = "tmdb:popular:page:{$page}";

        return Cache::remember($cacheKey, now()->addHour(), function () use ($page) {
            $response = $this->client()->get('/movie/popular', [
                'page' => $page,
                'language' => 'en-US',
            ]);

            $response->throw(); // throws exception on 4xx/5xx

            return $response->json();
        });
    }

    public function getMovieDetails(int $movieId): array
    {
        $cacheKey = "tmdb:movie:{$movieId}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($movieId) {
            $response = $this->client()->get("/movie/{$movieId}", [
                'language' => 'en-US',
            ]);

            $response->throw();

            return $response->json();
        });
    }

    public function searchMovies(string $query, int $page = 1): array
    {
        $cacheKey = "tmdb:search:" . md5($query) . ":page:{$page}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query, $page) {
            $response = $this->client()->get('/search/movie', [
                'query' => $query,
                'page' => $page,
                'language' => 'en-US',
            ]);

            $response->throw();

            return $response->json();
        });
    }

    public function getGenres(): array
    {
        $cacheKey = "tmdb:genres";

        return Cache::remember($cacheKey, now()->addDay(), function () {
            $response = $this->client()->get('/genre/movie/list', [
                'language' => 'en-US',
            ]);

            $response->throw();

            return $response->json();
        });
    }
}
