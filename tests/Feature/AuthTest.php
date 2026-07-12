<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_a_token(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJson(['status' => true, 'message' => 'Logged in successfully'])
            ->assertJsonStructure(['status', 'message', 'data' => ['token', 'token_type', 'user']]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonValidationErrors('email');
    }

    public function test_protected_route_rejects_request_without_token(): void
    {
        $response = $this->getJson('/api/customers');

        $response->assertStatus(401)
            ->assertJson(['status' => false, 'message' => 'Unauthenticated.']);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/logout');

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertCount(0, $user->fresh()->tokens);
    }
}
