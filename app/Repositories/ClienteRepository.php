<?php

namespace App\Repositories;

use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClienteRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Cliente::orderBy('nombre', 'asc')->paginate($perPage);
    }

    public function findById(int $id): ?Cliente
    {
        return Cliente::find($id);
    }

    public function findByTelefono(string $telefono): ?Cliente
    {
        return Cliente::where('telefono', $telefono)->first();
    }

    public function create(array $data): Cliente
    {
        return Cliente::create($data);
    }

    public function update(Cliente $cliente, array $data): Cliente
    {
        $cliente->update($data);
        return $cliente->fresh();
    }

    public function delete(Cliente $cliente): void
    {
        $cliente->delete();
    }

    public function getPedidos(Cliente $cliente, int $perPage = 15): LengthAwarePaginator
    {
        return $cliente->pedidos()->orderBy('fecha_pedido', 'desc')->paginate($perPage);
    }
}
