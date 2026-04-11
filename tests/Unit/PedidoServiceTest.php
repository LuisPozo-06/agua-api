<?php

namespace Tests\Unit;

use App\Models\Chofer;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PedidoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PedidoService(
            new \App\Repositories\PedidoRepository
        );
    }

    public function test_create_pedido(): void
    {
        $cliente = Cliente::factory()->create();

        $data = [
            'cliente_id' => $cliente->id,
            'cantidad_agua' => 20,
            'direccion_entrega' => 'Av. Test 123',
            'prioridad' => 1,
        ];

        $pedido = $this->service->create($data);

        $this->assertDatabaseHas('pedidos', [
            'cliente_id' => $cliente->id,
            'cantidad_agua' => 20,
            'estado' => 'Pendiente',
        ]);
    }

    public function test_create_pedido_asigna_estado_pendiente_por_defecto(): void
    {
        $cliente = Cliente::factory()->create();

        $data = [
            'cliente_id' => $cliente->id,
            'cantidad_agua' => 10,
            'direccion_entrega' => 'Av. Test',
            'prioridad' => 2,
        ];

        $pedido = $this->service->create($data);

        $this->assertEquals('Pendiente', $pedido->estado);
    }

    public function test_actualizar_estado(): void
    {
        $pedido = Pedido::factory()->create(['estado' => 'Pendiente']);

        $result = $this->service->actualizarEstado($pedido, 'En proceso');

        $this->assertEquals('En proceso', $result->estado);
        $this->assertNotNull($result->estado_updated_at);
    }

    public function test_actualizar_estado_entregado_libera_chofer(): void
    {
        $chofer = Chofer::factory()->create(['estado_asignacion' => 'ocupado']);
        $pedido = Pedido::factory()->create([
            'estado' => 'En proceso',
            'chofer_id' => $chofer->id,
        ]);

        $this->service->actualizarEstado($pedido, 'Entregado');

        $this->assertEquals('disponible', $chofer->fresh()->estado_asignacion);
    }

    public function test_actualizar_prioridad(): void
    {
        $pedido = Pedido::factory()->create(['prioridad' => 1]);

        $result = $this->service->actualizarPrioridad($pedido, 3);

        $this->assertEquals(3, $result->prioridad);
    }

    public function test_actualizar_nota(): void
    {
        $pedido = Pedido::factory()->create(['nota' => null]);

        $result = $this->service->actualizarNota($pedido, 'Llamar al llegar');

        $this->assertEquals('Llamar al llegar', $result->nota);
    }

    public function test_actualizar_direccion(): void
    {
        $pedido = Pedido::factory()->create();

        $result = $this->service->actualizarDireccion($pedido, 'Nueva Dirección 456');

        $this->assertEquals('Nueva Dirección 456', $result->direccion_entrega);
    }

    public function test_cancelar_pedido(): void
    {
        $pedido = Pedido::factory()->create(['estado' => 'Pendiente']);

        $result = $this->service->cancelar($pedido);

        $this->assertEquals('Cancelado', $result->estado);
    }

    public function test_actualizar_estado_pago(): void
    {
        $pedido = Pedido::factory()->create(['estado_pago' => 'Pendiente']);

        $result = $this->service->actualizarEstadoPago($pedido, 'Pagado');

        $this->assertEquals('Pagado', $result->estado_pago);
        $this->assertNotNull($result->estado_pago_updated_at);
    }

    public function test_asignar_chofer(): void
    {
        $chofer = Chofer::factory()->create([
            'is_active' => true,
            'estado_asignacion' => 'disponible',
        ]);
        $pedido = Pedido::factory()->create(['chofer_id' => null]);

        $result = $this->service->asignarChofer($pedido, $chofer->id);

        $this->assertEquals($chofer->id, $result->chofer_id);
        $this->assertEquals('En proceso', $result->estado);
    }

    public function test_asignar_chofer_no_disponible(): void
    {
        $chofer = Chofer::factory()->create([
            'is_active' => true,
            'estado_asignacion' => 'ocupado',
        ]);
        $pedido = Pedido::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El chofer seleccionado no está disponible.');

        $this->service->asignarChofer($pedido, $chofer->id);
    }

    public function test_asignar_chofer_inactivo(): void
    {
        $chofer = Chofer::factory()->create([
            'is_active' => false,
            'estado_asignacion' => 'disponible',
        ]);
        $pedido = Pedido::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El chofer seleccionado no está disponible.');

        $this->service->asignarChofer($pedido, $chofer->id);
    }

    public function test_guardar_comprobante(): void
    {
        $pedido = Pedido::factory()->create([
            'estado_pago' => 'Pendiente',
            'comprobante_url' => null,
        ]);

        $result = $this->service->guardarComprobante(
            $pedido,
            'https://res.cloudinary.com/demo/image/upload/v123/abc.jpg'
        );

        $this->assertEquals('https://res.cloudinary.com/demo/image/upload/v123/abc.jpg', $result->comprobante_url);
        $this->assertEquals('Pagado', $result->estado_pago);
    }

    public function test_delete_pedido(): void
    {
        $pedido = Pedido::factory()->create();

        $this->service->delete($pedido);

        $this->assertSoftDeleted('pedidos', ['id' => $pedido->id]);
    }
}
