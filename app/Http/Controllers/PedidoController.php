<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePrioridadRequest;
use App\Http\Requests\UpdateNotaRequest;
use App\Http\Requests\UpdateDireccionRequest;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class PedidoController extends Controller
{
    protected PedidoService $pedidoService;

    public function __construct(PedidoService $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }

    #[OA\Get(
        path: "/api/pedidos",
        summary: "Listar pedidos",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\Parameter(name: "per_page", in: "query", required: false, description: "Registros por página", schema: new OA\Schema(type: "integer", example: 10))]
    #[OA\Parameter(name: "desde", in: "query", required: false, description: "Fecha de inicio (YYYY-MM-DD)", schema: new OA\Schema(type: "string", format: "date"))]
    #[OA\Parameter(name: "hasta", in: "query", required: false, description: "Fecha de fin (YYYY-MM-DD)", schema: new OA\Schema(type: "string", format: "date"))]
    #[OA\Response(response: 200, description: "Lista de pedidos paginada")]
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $filters = [
            'desde' => $request->query('desde'),
            'hasta' => $request->query('hasta'),
        ];

        $pedidos = $this->pedidoService->getAll($filters, $perPage);

        return response()->json([
            'data' => $pedidos->items(),
            'meta' => [
                'current_page' => $pedidos->currentPage(),
                'per_page' => $pedidos->perPage(),
                'total' => $pedidos->total(),
                'last_page' => $pedidos->lastPage(),
            ]
        ]);
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
        $pedido = $this->pedidoService->create($request->validated());
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
        $pedido = $this->pedidoService->findById($id);
        
        if (!$pedido) {
            return response()->json(['mensaje' => 'Pedido no encontrado'], 404);
        }

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
        $pedido = $this->pedidoService->update($pedido, $request->only(['estado']));
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
        $this->pedidoService->delete($pedido);

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
    #[OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Lista de pedidos filtrados")]
    public function pedidosPorEstado(Request $request, $estado)
    {
        $perPage = $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $pedidos = $this->pedidoService->getByEstado($estado, $perPage);

        return response()->json([
            'data' => $pedidos->items(),
            'meta' => [
                'current_page' => $pedidos->currentPage(),
                'per_page' => $pedidos->perPage(),
                'total' => $pedidos->total(),
                'last_page' => $pedidos->lastPage(),
            ]
        ]);
    }

    #[OA\Get(
        path: "/api/pedidos/pendientes",
        summary: "Listar pedidos pendientes",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Pedidos pendientes paginados")]
    public function pendientes(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $pedidos = $this->pedidoService->getPendientes($perPage);

        return response()->json([
            'data' => $pedidos->items(),
            'meta' => [
                'current_page' => $pedidos->currentPage(),
                'per_page' => $pedidos->perPage(),
                'total' => $pedidos->total(),
                'last_page' => $pedidos->lastPage(),
            ]
        ]);
    }

    #[OA\Get(
        path: "/api/pedidos/prioridad/{nivel}",
        summary: "Filtrar pedidos por prioridad",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "nivel", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Lista filtrada")]
    public function pedidosPorPrioridad(Request $request, $nivel)
    {
        $perPage = $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $pedidos = $this->pedidoService->getByPrioridad($nivel, $perPage);

        return response()->json([
            'data' => $pedidos->items(),
            'meta' => [
                'current_page' => $pedidos->currentPage(),
                'per_page' => $pedidos->perPage(),
                'total' => $pedidos->total(),
                'last_page' => $pedidos->lastPage(),
            ]
        ]);
    }

    #[OA\Get(
        path: "/api/pedidos/priorizados",
        summary: "Lista de pedidos mayor prioridad primero",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Pedidos ordenados")]
    public function priorizados(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $pedidos = $this->pedidoService->getPriorizados($perPage);

        return response()->json([
            'data' => $pedidos->items(),
            'meta' => [
                'current_page' => $pedidos->currentPage(),
                'per_page' => $pedidos->perPage(),
                'total' => $pedidos->total(),
                'last_page' => $pedidos->lastPage(),
            ]
        ]);
    }

    #[OA\Get(
        path: "/api/pedidos/hoy",
        summary: "Pedidos del día de hoy",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Pedidos de hoy")]
    public function hoy(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $pedidos = $this->pedidoService->getHoy($perPage);

        return response()->json([
            'data' => $pedidos->items(),
            'meta' => [
                'current_page' => $pedidos->currentPage(),
                'per_page' => $pedidos->perPage(),
                'total' => $pedidos->total(),
                'last_page' => $pedidos->lastPage(),
            ]
        ]);
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
        $pedido = $this->pedidoService->actualizarEstado($pedido, $request->estado);

        return response()->json([
            "mensaje" => "Estado actualizado",
            "pedido" => $pedido
        ]);
    }

    #[OA\Put(
        path: "/api/pedidos/{id}/estado-pago",
        summary: "Actualiza el estado de pago del pedido",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["estado_pago"],
            properties: [
                new OA\Property(property: "estado_pago", type: "string", example: "Pagado")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Estado de pago actualizado")]
    public function actualizarEstadoPago(Request $request, $id)
    {
        $request->validate([
            'estado_pago' => 'required|in:Pendiente,Pagado'
        ]);

        $pedido = Pedido::findOrFail($id);
        $pedido = $this->pedidoService->actualizarEstadoPago($pedido, $request->estado_pago);

        return response()->json([
            "mensaje" => "Estado de pago actualizado",
            "pedido" => $pedido
        ]);
    }

    #[OA\Post(
        path: "/api/pedidos/{id}/comprobante",
        summary: "Guardar URL del comprobante de pago (Cloudinary)",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["comprobante_url"],
            properties: [
                new OA\Property(property: "comprobante_url", type: "string", example: "https://res.cloudinary.com/demo/image/upload/v123/abc.jpg")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "URL del comprobante guardada")]
    public function guardarComprobante(Request $request, $id)
    {
        $request->validate([
            'comprobante_url' => 'required|url|max:500'
        ]);

        $pedido = Pedido::findOrFail($id);
        $pedido = $this->pedidoService->guardarComprobante($pedido, $request->comprobante_url);

        return response()->json([
            "mensaje" => "Comprobante guardado",
            "pedido" => $pedido
        ]);
    }

    #[OA\Get(
        path: "/api/pedidos/pago-pendiente",
        summary: "Listar pedidos con pago pendiente",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Lista de pedidos con pago pendiente")]
    public function pagoPendiente(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $pedidos = $this->pedidoService->getPagoPendiente($perPage);

        return response()->json([
            'data' => $pedidos->items(),
            'meta' => [
                'current_page' => $pedidos->currentPage(),
                'per_page' => $pedidos->perPage(),
                'total' => $pedidos->total(),
                'last_page' => $pedidos->lastPage(),
            ]
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
        $pedido = $this->pedidoService->actualizarPrioridad($pedido, $request->prioridad);

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
        $pedido = $this->pedidoService->actualizarNota($pedido, $request->nota);

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
        $pedido = $this->pedidoService->actualizarDireccion($pedido, $request->direccion_entrega);

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
        $pedido = $this->pedidoService->cancelar($pedido);

        return response()->json([
            "mensaje" => "Pedido cancelado",
            "pedido" => $pedido
        ]);
    }
    #[OA\Put(
        path: "/api/pedidos/{id}/asignar-chofer",
        summary: "Asignar chofer a un pedido",
        security: [["sanctum" => []]],
        tags: ["Pedidos"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["chofer_id"],
            properties: [
                new OA\Property(property: "chofer_id", type: "integer", example: 1)
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Chofer asignado correctamente")]
    public function asignarChofer(Request $request, $id)
    {
        $request->validate([
            'chofer_id' => 'required|exists:chofers,id'
        ]);

        $pedido = Pedido::findOrFail($id);

        try {
            $pedido = $this->pedidoService->asignarChofer($pedido, $request->chofer_id);
            return response()->json([
                "mensaje" => "Chofer asignado correctamente",
                "pedido" => $pedido->load('chofer')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "mensaje" => $e->getMessage()
            ], 400);
        }
    }
}
