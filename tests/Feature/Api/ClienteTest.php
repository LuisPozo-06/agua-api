<?php

namespace Tests\Feature\Api;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_list_clientes(): void
    {
        Cliente::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/clientes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page']
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_create_cliente(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/clientes', [
                'nombre' => 'Empresa Test',
                'telefono' => '999888777',
                'direccion' => 'Av. Test 123',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'nombre' => 'Empresa Test',
                'telefono' => '999888777',
            ]);

        $this->assertDatabaseHas('clientes', [
            'telefono' => '999888777',
        ]);
    }

    public function test_create_cliente_unique_telefono(): void
    {
        Cliente::factory()->create(['telefono' => '999888777']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/clientes', [
                'nombre' => 'Empresa Test',
                'telefono' => '999888777',
                'direccion' => 'Av. Test 123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['telefono']);
    }

    public function test_show_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/clientes/{$cliente->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
            ]);
    }

    public function test_update_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/clientes/{$cliente->id}", [
                'nombre' => 'Nuevo Nombre',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['nombre' => 'Nuevo Nombre']);
    }

    public function test_delete_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/clientes/{$cliente->id}");

        $response->assertStatus(200)
            ->assertJson(['mensaje' => 'Cliente eliminado correctamente']);

        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }

    public function test_cliente_pedidos(): void
    {
        $cliente = Cliente::factory()->create();
        
        $cliente->pedidos()->createMany([
            [
                'cantidad_agua' => 20,
                'direccion_entrega' => 'Av. 1',
                'prioridad' => 1,
                'estado' => 'Pendiente',
                'estado_pago' => 'Pendiente',
                'fecha_pedido' => now(),
            ],
            [
                'cantidad_agua' => 30,
                'direccion_entrega' => 'Av. 2',
                'prioridad' => 2,
                'estado' => 'Entregado',
                'estado_pago' => 'Pagado',
                'fecha_pedido' => now(),
            ],
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/clientes/{$cliente->id}/pedidos");

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_buscar_cliente_por_telefono(): void
    {
        $cliente = Cliente::factory()->create(['telefono' => '999888777']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/clientes/telefono/999888777');

        $response->assertStatus(200)
            ->assertJsonFragment(['telefono' => '999888777']);
    }
}
