<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_user(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Juan Perez',
            'email' => 'juan@test.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user',
                'token'
            ])
            ->assertJson([
                'user' => [
                    'name' => 'Juan Perez',
                    'email' => 'juan@test.com',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'juan@test.com',
        ]);
    }

    public function test_register_validation_required(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_validation_unique_email(): void
    {
        User::factory()->create(['email' => 'juan@test.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Juan Perez',
            'email' => 'juan@test.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_user(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'token'
            ]);
    }

    public function test_login_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_logout_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('API Token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['mensaje' => 'Sesión cerrada correctamente']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_unauthenticated_access(): void
    {
        $response = $this->getJson('/api/clientes');

        $response->assertStatus(401);
    }
}
