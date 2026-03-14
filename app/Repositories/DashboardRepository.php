<?php

namespace App\Repositories;

use App\Models\Pedido;

class DashboardRepository
{
    public function getCountByEstado(string $estado): int
    {
        return Pedido::where('estado', $estado)->count();
    }

    public function getCountByEstadoPago(string $estadoPago): int
    {
        return Pedido::where('estado_pago', $estadoPago)->count();
    }

    public function getTotalCount(): int
    {
        return Pedido::count();
    }
}
