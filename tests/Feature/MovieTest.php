<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MovieTest extends TestCase
{
    public function test_can_get_popular_movies(): void
    {
        Http::fake([
            'api.themoviedb.org/*' => Http::response([
                'page' => 1,
                'results' => [
                    ['id' => 1, 'title' => 'Movie 1', 'vote_average' => 8.5],
                ],
                'total_pages' => 10,
                'total_results' => 200,
            ], 200),
        ]);

        $response = $this->getJson('/api/movies/popular');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [['id', 'title', 'rating', 'poster_url']],
                'meta' => ['page', 'total_pages', 'total_results'],
            ]);
    }

    public function test_search_requires_query_param(): void
    {
        $response = $this->getJson('/api/movies/search');

        $response->assertStatus(422);
    }
}
