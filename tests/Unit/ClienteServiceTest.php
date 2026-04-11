<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Services\ClienteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ClienteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClienteService(
            new \App\Repositories\ClienteRepository
        );
    }

    public function test_create_cliente(): void
    {
        $data = [
            'nombre' => 'Empresa Test',
            'telefono' => '999888777',
            'direccion' => 'Av. Test 123',
        ];

        $cliente = $this->service->create($data);

        $this->assertDatabaseHas('clientes', [
            'nombre' => 'Empresa Test',
            'telefono' => '999888777',
        ]);
    }

    public function test_find_cliente_by_id(): void
    {
        $cliente = Cliente::factory()->create();

        $result = $this->service->findById($cliente->id);

        $this->assertNotNull($result);
        $this->assertEquals($cliente->id, $result->id);
    }

    public function test_find_cliente_by_id_not_found(): void
    {
        $result = $this->service->findById(99999);

        $this->assertNull($result);
    }

    public function test_find_cliente_by_telefono(): void
    {
        $cliente = Cliente::factory()->create(['telefono' => '999888777']);

        $result = $this->service->findByTelefono('999888777');

        $this->assertNotNull($result);
        $this->assertEquals('999888777', $result->telefono);
    }

    public function test_find_cliente_by_telefono_not_found(): void
    {
        $result = $this->service->findByTelefono('000000000');

        $this->assertNull($result);
    }

    public function test_update_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $result = $this->service->update($cliente, ['nombre' => 'Nuevo Nombre']);

        $this->assertEquals('Nuevo Nombre', $result->nombre);
    }

    public function test_delete_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $this->service->delete($cliente);

        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }

    public function test_get_pedidos(): void
    {
        $cliente = Cliente::factory()->create();

        Pedido::factory()->count(3)->create([
            'cliente_id' => $cliente->id,
        ]);

        $pedidos = $this->service->getPedidos($cliente);

        $this->assertCount(3, $pedidos);
    }

    public function test_get_pedidos_empty(): void
    {
        $cliente = Cliente::factory()->create();

        $pedidos = $this->service->getPedidos($cliente);

        $this->assertCount(0, $pedidos);
    }

    public function test_get_all_clientes(): void
    {
        Cliente::factory()->count(5)->create();

        $result = $this->service->getAll(10);

        $this->assertCount(5, $result);
    }
}
