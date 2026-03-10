<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use Illuminate\Http\Request;
use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePrioridadRequest;
use App\Http\Requests\UpdateNotaRequest;
use App\Http\Requests\UpdateDireccionRequest;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class PedidoController extends Controller
{
    #[OA\Get(
        path: "/api/pedidos",
        summary: "Listar pedidos",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "desde", in: "query", required: false, description: "Fecha de inicio (YYYY-MM-DD)", schema: new OA\Schema(type: "string", format: "date"))]
    #[OA\Parameter(name: "hasta", in: "query", required: false, description: "Fecha de fin (YYYY-MM-DD)", schema: new OA\Schema(type: "string", format: "date"))]
    #[OA\Response(response: 200, description: "Lista de pedidos")]
    public function index(Request $request)
    {
        $query = Pedido::with('cliente');

        if ($request->has('desde')) {
            $query->whereDate('fecha_pedido', '>=', $request->query('desde'));
        }

        if ($request->has('hasta')) {
            $query->whereDate('fecha_pedido', '<=', $request->query('hasta'));
        }

        $pedidos = $query->orderBy('fecha_pedido', 'desc')->get();

        return response()->json($pedidos);
    }

    #[OA\Post(
        path: "/api/pedidos",
        summary: "Crear un pedido básico",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["cliente_id", "cantidad_agua", "direccion_entrega", "prioridad"],
            properties: [
                new OA\Property(property: "cliente_id", type: "integer", example: 1),
                new OA\Property(property: "cantidad_agua", type: "integer", example: 20),
                new OA\Property(property: "direccion_entrega", type: "string", example: "Mz F Lt 1"),
                new OA\Property(property: "prioridad", type: "integer", example: 1),
                new OA\Property(property: "nota", type: "string", example: "Llamar al llegar")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Pedido creado")]
    public function store(StorePedidoRequest $request)
    {
        $pedido = Pedido::create([
            'cliente_id' => $request->cliente_id,
            'cantidad_agua' => $request->cantidad_agua,
            'direccion_entrega' => $request->direccion_entrega,
            'prioridad' => $request->prioridad,
            'estado' => 'Pendiente',
            'fecha_pedido' => now(),
            'nota' => $request->nota
        ]);

        return response()->json($pedido, 201);
    }

    #[OA\Get(
        path: "/api/pedidos/{id}",
        summary: "Obtener detalle de un pedido",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Operación exitosa")]
    #[OA\Response(response: 404, description: "Pedido no encontrado")]
    public function show($id)
    {
        $pedido = Pedido::with('cliente')->findOrFail($id);
        return response()->json($pedido);
    }

    #[OA\Put(
        path: "/api/pedidos/{id}",
        summary: "Actualizar un pedido",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "estado", type: "string", example: "En proceso")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Pedido actualizado")]
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $pedido->update([
            'estado' => $request->estado
        ]);

        return response()->json($pedido);
    }

    #[OA\Delete(
        path: "/api/pedidos/{id}",
        summary: "Eliminar un pedido (Soft Delete)",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Pedido eliminado")]
    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->delete();

        return response()->json([
            "mensaje" => "Pedido eliminado correctamente"
        ]);
    }

    #[OA\Post(
        path: "/api/pedido-completo",
        summary: "Crear cliente y pedido",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["telefono", "nombre", "direccion", "cantidad_agua", "direccion_entrega", "prioridad"],
            properties: [
                new OA\Property(property: "telefono", type: "string", example: "999888777"),
                new OA\Property(property: "nombre", type: "string", example: "Empresa S.A."),
                new OA\Property(property: "direccion", type: "string", example: "Av. Siempre Viva 123"),
                new OA\Property(property: "cantidad_agua", type: "integer", example: 10),
                new OA\Property(property: "direccion_entrega", type: "string", example: "Local 2"),
                new OA\Property(property: "prioridad", type: "integer", example: 1),
                new OA\Property(property: "nota", type: "string", example: "Traer sencillo")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Transacción completada")]
    public function crearPedidoCompleto(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $cliente = Cliente::firstOrCreate(
                ['telefono' => $request->telefono],
                [
                    'nombre' => $request->nombre,
                    'direccion' => $request->direccion
                ]
            );

            $pedido = Pedido::create([
                'cliente_id' => $cliente->id,
                'cantidad_agua' => $request->cantidad_agua,
                'direccion_entrega' => $request->direccion_entrega,
                'prioridad' => $request->prioridad,
                'estado' => 'Pendiente',
                'fecha_pedido' => now(),
                'nota' => $request->nota
            ]);

            return response()->json([
                "cliente" => $cliente,
                "pedido" => $pedido
            ], 201);
        });
    }

    #[OA\Get(
        path: "/api/pedidos/estado/{estado}",
        summary: "Obtener pedidos por un estado concreto",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "estado", in: "path", required: true, schema: new OA\Schema(type: "string", example: "Pendiente"))]
    #[OA\Response(response: 200, description: "Lista de pedidos filtrados")]
    public function pedidosPorEstado($estado)
    {
        $pedidos = Pedido::with('cliente')
            ->where('estado', $estado)
            ->get();

        return response()->json($pedidos);
    }

    #[OA\Get(
        path: "/api/pedidos/pendientes",
        summary: "Listar pedidos pendientes",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Response(response: 200, description: "Sólo retorna pedidos pendientes")]
    public function pendientes()
    {
        $pedidos = Pedido::with('cliente')
            ->where('estado', 'Pendiente')
            ->get();

        return response()->json($pedidos);
    }

    #[OA\Get(
        path: "/api/pedidos/prioridad/{nivel}",
        summary: "Filtrar pedidos por prioridad",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "nivel", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\Response(response: 200, description: "Lista filtrada")]
    public function pedidosPorPrioridad($nivel)
    {
        $pedidos = Pedido::with('cliente')
            ->where('prioridad', $nivel)
            ->orderBy('fecha_pedido', 'asc')
            ->get();

        return response()->json($pedidos);
    }

    #[OA\Get(
        path: "/api/pedidos/priorizados",
        summary: "Lista de pedidos mayor prioridad primero",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Response(response: 200, description: "Pedidos ordenados")]
    public function priorizados()
    {
        $pedidos = Pedido::with('cliente')
            ->orderBy('prioridad', 'asc')
            ->orderBy('fecha_pedido', 'asc')
            ->get();

        return response()->json($pedidos);
    }

    #[OA\Get(
        path: "/api/pedidos/hoy",
        summary: "Pedidos del día de hoy",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Response(response: 200, description: "Pedidos de hoy")]
    public function hoy()
    {
        $pedidos = Pedido::with('cliente')
            ->whereDate('fecha_pedido', Carbon::today())
            ->get();

        return response()->json($pedidos);
    }

    #[OA\Put(
        path: "/api/pedidos/{id}/estado",
        summary: "Actualiza sólo el estado",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["estado"],
            properties: [
                new OA\Property(property: "estado", type: "string", example: "En proceso")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Estado actualizado")]
    public function actualizarEstado(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->estado = $request->estado;
        $pedido->save();

        return response()->json([
            "mensaje" => "Estado actualizado",
            "pedido" => $pedido
        ]);
    }

    #[OA\Put(
        path: "/api/pedidos/{id}/prioridad",
        summary: "Modifica la prioridad del pedido",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["prioridad"],
            properties: [
                new OA\Property(property: "prioridad", type: "integer", example: 2)
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Prioridad actualizada")]
    public function actualizarPrioridad(UpdatePrioridadRequest $request, $id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->prioridad = $request->prioridad;
        $pedido->save();

        return response()->json([
            "mensaje" => "Prioridad actualizada",
            "pedido" => $pedido
        ]);
    }

    #[OA\Put(
        path: "/api/pedidos/{id}/nota",
        summary: "Modifica la nota de entrega",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "nota", type: "string", example: "Dejar con el portero")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Nota actualizada")]
    public function actualizarNota(UpdateNotaRequest $request, $id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->nota = $request->nota;
        $pedido->save();

        return response()->json([
            "mensaje" => "Nota actualizada",
            "pedido" => $pedido
        ]);
    }

    #[OA\Put(
        path: "/api/pedidos/{id}/direccion",
        summary: "Actualiza la direccion de entrega",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["direccion_entrega"],
            properties: [
                new OA\Property(property: "direccion_entrega", type: "string", example: "Av. Sol 555")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Dirección actualizada")]
    public function actualizarDireccion(UpdateDireccionRequest $request, $id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->direccion_entrega = $request->direccion_entrega;
        $pedido->save();

        return response()->json([
            "mensaje" => "Dirección actualizada",
            "pedido" => $pedido
        ]);
    }

    #[OA\Put(
        path: "/api/pedidos/{id}/cancelar",
        summary: "Cancelar un pedido",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Estado cambiado a Cancelado")]
    public function cancelar($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->estado = 'Cancelado';
        $pedido->save();

        return response()->json([
            "mensaje" => "Pedido cancelado",
            "pedido" => $pedido
        ]);
    }
}
