<?php

namespace App\Repositories;

use App\Models\Pedido;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PedidoRepository
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Pedido::with('cliente');

        if (isset($filters['desde'])) {
            $query->whereDate('fecha_pedido', '>=', $filters['desde']);
        }

        if (isset($filters['hasta'])) {
            $query->whereDate('fecha_pedido', '<=', $filters['hasta']);
        }

        if (isset($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (isset($filters['prioridad'])) {
            $query->where('prioridad', $filters['prioridad']);
        }

        return $query->orderBy('fecha_pedido', 'desc')->paginate($perPage);
    }

    public function getByEstado(string $estado, int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::with('cliente')
            ->where('estado', $estado)
            ->orderBy('prioridad', 'asc')
            ->orderBy('fecha_pedido', 'asc')
            ->paginate($perPage);
    }

    public function getPendientes(int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::with('cliente')
            ->where('estado', 'Pendiente')
            ->orderBy('prioridad', 'asc')
            ->orderBy('fecha_pedido', 'asc')
            ->paginate($perPage);
    }

    public function getPriorizados(int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::with('cliente')
            ->orderBy('prioridad', 'asc')
            ->orderBy('fecha_pedido', 'asc')
            ->paginate($perPage);
    }

    public function getHoy(int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::with('cliente')
            ->whereDate('fecha_pedido', now()->toDateString())
            ->orderBy('prioridad', 'asc')
            ->orderBy('fecha_pedido', 'asc')
            ->paginate($perPage);
    }

    public function getPagoPendiente(int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::with('cliente')
            ->where('estado_pago', 'Pendiente')
            ->orderBy('prioridad', 'asc')
            ->orderBy('fecha_pedido', 'asc')
            ->paginate($perPage);
    }

    public function getByPrioridad(int $nivel, int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::with('cliente')
            ->where('prioridad', $nivel)
            ->orderBy('fecha_pedido', 'asc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Pedido
    {
        return Pedido::with('cliente')->find($id);
    }

    public function create(array $data): Pedido
    {
        return Pedido::create($data);
    }

    public function update(Pedido $pedido, array $data): Pedido
    {
        $pedido->update($data);
        return $pedido->fresh();
    }

    public function delete(Pedido $pedido): void
    {
        $pedido->delete();
    }
}
