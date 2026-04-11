<?php

namespace Tests\Feature\Api;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoTest extends TestCase
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

    public function test_actualizar_estado_pago(): void
    {
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
            'estado_pago' => 'Pendiente',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/estado-pago", [
                'estado_pago' => 'Pagado',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'estado_pago' => 'Pagado',
                'mensaje' => 'Estado de pago actualizado',
            ]);

        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'estado_pago' => 'Pagado',
        ]);
    }

    public function test_actualizar_estado_pago_invalid(): void
    {
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
            'estado_pago' => 'Pendiente',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/estado-pago", [
                'estado_pago' => 'EstadoInvalido',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['estado_pago']);
    }

    public function test_actualizar_estado_pago_required(): void
    {
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pedidos/{$pedido->id}/estado-pago", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['estado_pago']);
    }

    public function test_guardar_comprobante(): void
    {
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
            'estado_pago' => 'Pendiente',
            'comprobante_url' => null,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/pedidos/{$pedido->id}/comprobante", [
                'comprobante_url' => 'https://res.cloudinary.com/demo/image/upload/v123/abc.jpg',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'mensaje' => 'Comprobante guardado',
            ]);

        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'comprobante_url' => 'https://res.cloudinary.com/demo/image/upload/v123/abc.jpg',
            'estado_pago' => 'Pagado',
        ]);
    }

    public function test_guardar_comprobante_invalid_url(): void
    {
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/pedidos/{$pedido->id}/comprobante", [
                'comprobante_url' => 'not-a-url',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comprobante_url']);
    }

    public function test_guardar_comprobante_required(): void
    {
        $pedido = Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/pedidos/{$pedido->id}/comprobante", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comprobante_url']);
    }

    public function test_pedidos_pago_pendiente(): void
    {
        Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
            'estado_pago' => 'Pendiente',
        ]);
        Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
            'estado_pago' => 'Pendiente',
        ]);
        Pedido::factory()->create([
            'cliente_id' => $this->cliente->id,
            'estado_pago' => 'Pagado',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/pedidos/pago-pendiente');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_pedidos_pago_pendiente_empty(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/pedidos/pago-pendiente');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }
}
