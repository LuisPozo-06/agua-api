<?php

namespace Tests\Feature\Api;

use App\Models\Chofer;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsignacionChoferTest extends TestCase
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

    public function test_asignar_chofer(): void
    {
        $chofer = Chofer::factory()->create([
            'is_active' => true,
            'estado_asignacion' => 'disponible',
        ]);
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
            'chofer_id' => null,
            'estado' => 'Pendiente',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/asignar-chofer", [
                'chofer_id' => $chofer->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'mensaje' => 'Chofer asignado correctamente',
            ]);

        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'chofer_id' => $chofer->id,
            'estado' => 'En proceso',
        ]);

        $this->assertDatabaseHas('chofers', [
            'id' => $chofer->id,
            'estado_asignacion' => 'ocupado',
        ]);
    }

    public function test_asignar_chofer_inexistente(): void
    {
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/asignar-chofer", [
                'chofer_id' => 99999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['chofer_id']);
    }

    public function test_asignar_chofer_no_disponible(): void
    {
        $chofer = Chofer::factory()->create([
            'is_active' => true,
            'estado_asignacion' => 'ocupado',
        ]);
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/asignar-chofer", [
                'chofer_id' => $chofer->id,
            ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'mensaje' => 'El chofer seleccionado no está disponible.',
            ]);
    }

    public function test_asignar_chofer_inactivo(): void
    {
        $chofer = Chofer::factory()->create([
            'is_active' => false,
            'estado_asignacion' => 'disponible',
        ]);
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/asignar-chofer", [
                'chofer_id' => $chofer->id,
            ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'mensaje' => 'El chofer seleccionado no está disponible.',
            ]);
    }

    public function test_actualizar_chofer_asignado(): void
    {
        $choferAnterior = Chofer::factory()->create([
            'is_active' => true,
            'estado_asignacion' => 'ocupado',
        ]);
        $choferNuevo = Chofer::factory()->create([
            'is_active' => true,
            'estado_asignacion' => 'disponible',
        ]);
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
            'chofer_id' => $choferAnterior->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/asignar-chofer", [
                'chofer_id' => $choferNuevo->id,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'chofer_id' => $choferNuevo->id,
        ]);

        $this->assertDatabaseHas('chofers', [
            'id' => $choferAnterior->id,
            'estado_asignacion' => 'disponible',
        ]);

        $this->assertDatabaseHas('chofers', [
            'id' => $choferNuevo->id,
            'estado_asignacion' => 'ocupado',
        ]);
    }

    public function test_pedido_tiene_chofer_cargado(): void
    {
        $chofer = Chofer::factory()->create();
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
            'chofer_id' => $chofer->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/pedidos/{$pedido->id}");

        $response->assertStatus(200);
    }

    public function test_asignar_chofer_required(): void
    {
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/asignar-chofer", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['chofer_id']);
    }
}
