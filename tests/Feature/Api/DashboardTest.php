<?php

namespace Tests\Feature\Api;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_stats(): void
    {
        $cliente = Cliente::factory()->create();
        
        $cliente->pedidos()->createMany([
            ['estado' => 'Pendiente', 'cantidad_agua' => 10, 'direccion_entrega' => 'A', 'prioridad' => 1, 'estado_pago' => 'Pendiente', 'fecha_pedido' => now()],
            ['estado' => 'Pendiente', 'cantidad_agua' => 10, 'direccion_entrega' => 'B', 'prioridad' => 1, 'estado_pago' => 'Pendiente', 'fecha_pedido' => now()],
            ['estado' => 'En proceso', 'cantidad_agua' => 10, 'direccion_entrega' => 'C', 'prioridad' => 1, 'estado_pago' => 'Pendiente', 'fecha_pedido' => now()],
            ['estado' => 'Entregado', 'cantidad_agua' => 10, 'direccion_entrega' => 'D', 'prioridad' => 1, 'estado_pago' => 'Pagado', 'fecha_pedido' => now()],
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'pedidos_pendientes' => 2,
                'pedidos_proceso' => 1,
                'pedidos_entregados' => 1,
                'total_pedidos' => 4,
            ]);
    }

    public function test_dashboard_empty(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'pedidos_pendientes' => 0,
                'pedidos_proceso' => 0,
                'pedidos_entregados' => 0,
                'pagos_pendientes' => 0,
                'total_pedidos' => 0,
            ]);
    }
}
