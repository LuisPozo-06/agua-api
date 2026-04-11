<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_list_usuarios(): void
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/usuarios');

        $response->assertStatus(200)
            ->assertJsonCount(4);
    }

    public function test_create_usuario(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/usuarios', [
                'name' => 'Nuevo Usuario',
                'email' => 'nuevo@test.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Nuevo Usuario',
                'email' => 'nuevo@test.com',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@test.com',
        ]);
    }

    public function test_create_usuario_con_permisos(): void
    {
        Permission::create(['name' => 'ver pedidos']);
        Permission::create(['name' => 'crear pedidos']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/usuarios', [
                'name' => 'Usuario con Permisos',
                'email' => 'permisos@test.com',
                'password' => 'password123',
                'permissions' => ['ver pedidos', 'crear pedidos'],
            ]);

        $response->assertStatus(201);
    }

    public function test_create_usuario_email_unico(): void
    {
        User::factory()->create(['email' => 'existente@test.com']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/usuarios', [
                'name' => 'Usuario Repetido',
                'email' => 'existente@test.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_show_usuario(): void
    {
        $usuario = User::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/usuarios/{$usuario->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $usuario->id,
                'name' => $usuario->name,
            ]);
    }

    public function test_update_usuario(): void
    {
        $usuario = User::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/usuarios/{$usuario->id}", [
                'name' => 'Nombre Actualizado',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Nombre Actualizado']);
    }

    public function test_update_password(): void
    {
        $usuario = User::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/usuarios/{$usuario->id}", [
                'password' => 'nuevapass123',
            ]);

        $response->assertStatus(200);
    }

    public function test_delete_usuario(): void
    {
        $usuario = User::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/usuarios/{$usuario->id}");

        $response->assertStatus(204);
    }

    public function test_list_permisos(): void
    {
        Permission::create(['name' => 'ver pedidos']);
        Permission::create(['name' => 'crear pedidos']);
        Permission::create(['name' => 'eliminar pedidos']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/permisos');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }
}
