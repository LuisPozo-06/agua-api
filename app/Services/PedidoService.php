<?php

namespace App\Services;

use App\Models\Pedido;
use App\Repositories\PedidoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PedidoService
{
    protected PedidoRepository $repository;

    public function __construct(PedidoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    public function getByEstado(string $estado, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getByEstado($estado, $perPage);
    }

    public function getPendientes(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPendientes($perPage);
    }

    public function getPriorizados(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPriorizados($perPage);
    }

    public function getHoy(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getHoy($perPage);
    }

    public function getPagoPendiente(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPagoPendiente($perPage);
    }

    public function getByPrioridad(int $nivel, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getByPrioridad($nivel, $perPage);
    }

    public function findById(int $id): ?Pedido
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Pedido
    {
        $data['estado'] = $data['estado'] ?? 'Pendiente';
        $data['fecha_pedido'] = $data['fecha_pedido'] ?? now();
        
        return $this->repository->create($data);
    }

    public function update(Pedido $pedido, array $data): Pedido
    {
        return $this->repository->update($pedido, $data);
    }

    public function delete(Pedido $pedido): void
    {
        $this->repository->delete($pedido);
    }

    public function actualizarEstado(Pedido $pedido, string $estado): Pedido
    {
        $pedido->estado = $estado;
        $pedido->estado_updated_at = now();
        $pedido->save();

        if (in_array($estado, ['Entregado', 'Cancelado']) && $pedido->chofer_id) {
            $chofer = $pedido->chofer;
            if ($chofer) {
                $chofer->estado_asignacion = 'disponible';
                $chofer->save();
            }
        }

        return $pedido;
    }

    public function asignarChofer(Pedido $pedido, int $choferId): Pedido
    {
        $chofer = \App\Models\Chofer::findOrFail($choferId);

        if (!$chofer->is_active || $chofer->estado_asignacion !== 'disponible') {
            throw new \Exception('El chofer seleccionado no está disponible.');
        }

        // Liberar al chofer anterior si se está reasignando
        if ($pedido->chofer_id && $pedido->chofer_id !== $choferId) {
            $choferAnterior = $pedido->chofer;
            if ($choferAnterior) {
                $choferAnterior->estado_asignacion = 'disponible';
                $choferAnterior->save();
            }
        }

        $pedido->chofer_id = $chofer->id;
        $pedido->estado = 'En proceso'; // Opcional, dependiendo de la regla de negocio
        $pedido->estado_updated_at = now();
        $pedido->save();

        $chofer->estado_asignacion = 'ocupado';
        $chofer->save();

        return $pedido;
    }

    public function actualizarPrioridad(Pedido $pedido, int $prioridad): Pedido
    {
        $pedido->prioridad = $prioridad;
        $pedido->save();
        return $pedido;
    }

    public function actualizarNota(Pedido $pedido, ?string $nota): Pedido
    {
        $pedido->nota = $nota;
        $pedido->save();
        return $pedido;
    }

    public function actualizarDireccion(Pedido $pedido, string $direccion): Pedido
    {
        $pedido->direccion_entrega = $direccion;
        $pedido->save();
        return $pedido;
    }

    public function actualizarEstadoPago(Pedido $pedido, string $estadoPago): Pedido
    {
        $pedido->estado_pago = $estadoPago;
        $pedido->estado_pago_updated_at = now();
        $pedido->save();
        return $pedido;
    }

    public function cancelar(Pedido $pedido): Pedido
    {
        return $this->actualizarEstado($pedido, 'Cancelado');
    }

    public function guardarComprobante(Pedido $pedido, string $url): Pedido
    {
        $pedido->comprobante_url = $url;
        if ($pedido->estado_pago === 'Pendiente') {
            $pedido->estado_pago = 'Pagado';
            $pedido->estado_pago_updated_at = now();
        }
        $pedido->save();
        return $pedido;
    }
}
