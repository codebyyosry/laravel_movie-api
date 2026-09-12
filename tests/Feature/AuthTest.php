<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@test.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['user', 'access_token', 'token_type', 'expires_in'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ahmed@test.com',
        ]);
    }

    public function test_user_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'ahmed@test.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@test.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'ahmed@test.com',
            'password' => bcrypt('123456'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'ahmed@test.com',
            'password' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['access_token'],
            ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'ahmed@test.com',
            'password' => bcrypt('123456'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'ahmed@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_protected_route(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }
}
