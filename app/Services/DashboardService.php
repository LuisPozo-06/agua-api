<?php

namespace App\Services;

use App\Repositories\DashboardRepository;

class DashboardService
{
    protected DashboardRepository $repository;

    public function __construct(DashboardRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getMetrics(): array
    {
        return [
            "pedidos_pendientes" => $this->repository->getCountByEstado('Pendiente'),
            "pedidos_proceso"     => $this->repository->getCountByEstado('En proceso'),
            "pedidos_entregados"   => $this->repository->getCountByEstado('Entregado'),
            "pagos_pendientes"    => $this->repository->getCountByEstadoPago('Pendiente'),
            "total_pedidos"       => $this->repository->getTotalCount()
        ];
    }
}
