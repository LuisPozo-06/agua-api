<?php

namespace App\Services;

use App\Models\Cliente;
use App\Repositories\ClienteRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClienteService
{
    protected ClienteRepository $repository;

    public function __construct(ClienteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($perPage);
    }

    public function findById(int $id): ?Cliente
    {
        return $this->repository->findById($id);
    }

    public function findByTelefono(string $telefono): ?Cliente
    {
        return $this->repository->findByTelefono($telefono);
    }

    public function create(array $data): Cliente
    {
        return $this->repository->create($data);
    }

    public function update(Cliente $cliente, array $data): Cliente
    {
        return $this->repository->update($cliente, $data);
    }

    public function delete(Cliente $cliente): void
    {
        $this->repository->delete($cliente);
    }

    public function getPedidos(Cliente $cliente, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPedidos($cliente, $perPage);
    }
}
