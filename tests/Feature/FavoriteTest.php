<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_authenticated_user_can_add_favorite(): void
    {
        // Mock TMDB response so no real HTTP call happens
        Http::fake([
            'api.themoviedb.org/*' => Http::response([
                'id' => 155,
                'title' => 'The Dark Knight',
                'poster_path' => '/abc123.jpg',
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->postJson('/api/favorites', [
            'tmdb_movie_id' => 155,
        ], $this->authHeaders($user));

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'The Dark Knight',
                ],
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'tmdb_movie_id' => 155,
        ]);
    }

    public function test_cannot_add_duplicate_favorite(): void
    {
        Http::fake([
            'api.themoviedb.org/*' => Http::response(['id' => 155, 'title' => 'Test'], 200),
        ]);

        $user = User::factory()->create();

        Favorite::create([
            'user_id' => $user->id,
            'tmdb_movie_id' => 155,
            'title' => 'The Dark Knight',
        ]);

        $response = $this->postJson('/api/favorites', [
            'tmdb_movie_id' => 155,
        ], $this->authHeaders($user));

        $response->assertStatus(409);
    }

    public function test_user_can_list_own_favorites_only(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Favorite::create(['user_id' => $user1->id, 'tmdb_movie_id' => 155, 'title' => 'Movie A']);
        Favorite::create(['user_id' => $user2->id, 'tmdb_movie_id' => 200, 'title' => 'Movie B']);

        $response = $this->getJson('/api/favorites', $this->authHeaders($user1));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Movie A']);
    }

    public function test_guest_cannot_add_favorite(): void
    {
        $response = $this->postJson('/api/favorites', ['tmdb_movie_id' => 155]);

        $response->assertStatus(401);
    }

    public function test_user_can_remove_favorite(): void
    {
        $user = User::factory()->create();

        Favorite::create(['user_id' => $user->id, 'tmdb_movie_id' => 155, 'title' => 'Movie A']);

        $response = $this->deleteJson('/api/favorites/155', [], $this->authHeaders($user));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'tmdb_movie_id' => 155,
        ]);
    }
}
