<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'cantidad_agua' => fake()->numberBetween(1, 100),
            'direccion_entrega' => fake()->address(),
            'prioridad' => fake()->numberBetween(1, 3),
            'estado' => 'Pendiente',
            'estado_pago' => 'Pendiente',
            'fecha_pedido' => now(),
            'nota' => fake()->optional()->sentence(),
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'Entregado',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado_pago' => 'Pagado',
        ]);
    }
}
