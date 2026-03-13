<?php

namespace Tests\Feature\Api;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->cliente = Cliente::factory()->create();
    }

    public function test_list_pedidos(): void
    {
        Pedido::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/pedidos');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_list_pedidos_with_filters(): void
    {
        Pedido::factory()->create([
            'fecha_pedido' => now()->subDay(),
        ]);
        Pedido::factory()->create([
            'fecha_pedido' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/pedidos?desde=' . now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_create_pedido(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/pedidos', [
                'cliente_id' => $this->cliente->id,
                'cantidad_agua' => 20,
                'direccion_entrega' => 'Av. Test 123',
                'prioridad' => 1,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'cantidad_agua' => 20,
                'estado' => 'Pendiente',
            ]);

        $this->assertDatabaseHas('pedidos', [
            'cliente_id' => $this->cliente->id,
            'cantidad_agua' => 20,
        ]);
    }

    public function test_create_pedido_validation(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/pedidos', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cliente_id', 'cantidad_agua', 'direccion_entrega', 'prioridad']);
    }

    public function test_show_pedido(): void
    {
        $pedido = Pedido::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/pedidos/{$pedido->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $pedido->id]);
    }

    public function test_update_estado(): void
    {
        $pedido = Pedido::factory()->create(['estado' => 'Pendiente']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/estado", [
                'estado' => 'En proceso',
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'estado' => 'En proceso',
        ]);
    }

    public function test_update_prioridad(): void
    {
        $pedido = Pedido::factory()->create(['prioridad' => 1]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/prioridad", [
                'prioridad' => 3,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['prioridad' => 3]);
    }

    public function test_update_nota(): void
    {
        $pedido = Pedido::factory()->create(['nota' => null]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/nota", [
                'nota' => 'Llamar al llegar',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['nota' => 'Llamar al llegar']);
    }

    public function test_update_direccion(): void
    {
        $pedido = Pedido::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/direccion", [
                'direccion_entrega' => 'Nueva Dirección 456',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['direccion_entrega' => 'Nueva Dirección 456']);
    }

    public function test_cancel_pedido(): void
    {
        $pedido = Pedido::factory()->create(['estado' => 'Pendiente']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/cancelar");

        $response->assertStatus(200)
            ->assertJsonFragment(['estado' => 'Cancelado']);
    }

    public function test_filtrar_por_estado(): void
    {
        Pedido::factory()->create(['estado' => 'Pendiente']);
        Pedido::factory()->create(['estado' => 'Entregado']);
        Pedido::factory()->create(['estado' => 'Pendiente']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/pedidos/estado/Pendiente');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_pedidos_pendientes(): void
    {
        Pedido::factory()->create(['estado' => 'Pendiente']);
        Pedido::factory()->create(['estado' => 'Pendiente']);
        Pedido::factory()->create(['estado' => 'Entregado']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/pedidos/pendientes');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_pedidos_priorizados(): void
    {
        Pedido::factory()->create(['prioridad' => 3]);
        Pedido::factory()->create(['prioridad' => 1]);
        Pedido::factory()->create(['prioridad' => 2]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/pedidos/priorizados');

        $response->assertStatus(200);
        
        $pedidos = $response->json();
        $this->assertEquals(1, $pedidos[0]['prioridad']);
    }

    public function test_pedidos_hoy(): void
    {
        Pedido::factory()->create(['fecha_pedido' => now()]);
        Pedido::factory()->create(['fecha_pedido' => now()->subDay()]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/pedidos/hoy');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_pedidos_pago_pendiente(): void
    {
        Pedido::factory()->create(['estado_pago' => 'Pendiente']);
        Pedido::factory()->create(['estado_pago' => 'Pagado']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/pedidos/pago-pendiente');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_crear_pedido_completo(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/pedido-completo', [
                'telefono' => '999888777',
                'nombre' => 'Nueva Empresa',
                'direccion' => 'Av. Nueva 123',
                'cantidad_agua' => 25,
                'direccion_entrega' => 'Local 5',
                'prioridad' => 2,
            ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('clientes', ['telefono' => '999888777']);
        $this->assertDatabaseHas('pedidos', ['cantidad_agua' => 25]);
    }
}
