<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService(
            new \App\Repositories\DashboardRepository
        );
    }

    public function test_get_metrics(): void
    {
        $cliente = Cliente::factory()->create();

        Pedido::factory()->create(['cliente_id' => $cliente->id, 'estado' => 'Pendiente']);
        Pedido::factory()->create(['cliente_id' => $cliente->id, 'estado' => 'Pendiente']);
        Pedido::factory()->create(['cliente_id' => $cliente->id, 'estado' => 'En proceso']);
        Pedido::factory()->create(['cliente_id' => $cliente->id, 'estado' => 'Entregado']);
        Pedido::factory()->create(['cliente_id' => $cliente->id, 'estado' => 'Entregado']); // Segundo entregado
        Pedido::factory()->create(['cliente_id' => $cliente->id, 'estado' => 'Entregado', 'estado_pago' => 'Pendiente']); // Ya estaba incluido

        $metrics = $this->service->getMetrics();

        $this->assertEquals(2, $metrics['pedidos_pendientes']);
        $this->assertEquals(1, $metrics['pedidos_proceso']);
        $this->assertEquals(3, $metrics['pedidos_entregados']);
        $this->assertEquals(6, $metrics['total_pedidos']);
    }

    public function test_get_metrics_empty(): void
    {
        $metrics = $this->service->getMetrics();

        $this->assertEquals(0, $metrics['pedidos_pendientes']);
        $this->assertEquals(0, $metrics['pedidos_proceso']);
        $this->assertEquals(0, $metrics['pedidos_entregados']);
        $this->assertEquals(0, $metrics['pagos_pendientes']);
        $this->assertEquals(0, $metrics['total_pedidos']);
    }

    public function test_get_metrics_solo_pagos_pendientes(): void
    {
        $cliente = Cliente::factory()->create();

        Pedido::factory()->create([
            'cliente_id' => $cliente->id,
            'estado' => 'Entregado',
            'estado_pago' => 'Pendiente',
        ]);
        Pedido::factory()->create([
            'cliente_id' => $cliente->id,
            'estado' => 'Entregado',
            'estado_pago' => 'Pagado',
        ]);

        $metrics = $this->service->getMetrics();

        $this->assertEquals(1, $metrics['pagos_pendientes']);
    }
}
