<?php

namespace Database\Factories;

use App\Models\Chofer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChoferFactory extends Factory
{
    protected $model = Chofer::class;

    public function definition(): array
    {
        return [
            'nombres_completos' => fake()->name(),
            'telefono' => fake()->optional()->numerify('9########'),
            'is_active' => true,
            'estado_asignacion' => 'disponible',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function ocupado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado_asignacion' => 'ocupado',
        ]);
    }
}
