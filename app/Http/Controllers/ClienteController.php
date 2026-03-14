<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\ClienteService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ClienteController extends Controller
{
    protected ClienteService $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    #[OA\Get(
        path: "/api/clientes",
        summary: "Listar todos los clientes",
        security: [["sanctum" => []]],
        tags: ["Clientes"]
    )]
    #[OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\Parameter(name: "per_page", in: "query", required: false, description: "Registros por página", schema: new OA\Schema(type: "integer", example: 10))]
    #[OA\Response(response: 200, description: "Operación exitosa")]
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $clientes = $this->clienteService->getAll($perPage);

        return response()->json([
            'data' => $clientes->items(),
            'meta' => [
                'current_page' => $clientes->currentPage(),
                'per_page' => $clientes->perPage(),
                'total' => $clientes->total(),
                'last_page' => $clientes->lastPage(),
            ]
        ]);
    }

    #[OA\Post(
        path: "/api/clientes",
        summary: "Crear un nuevo cliente",
        security: [["sanctum" => []]],
        tags: ["Clientes"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["nombre", "telefono", "direccion"],
            properties: [
                new OA\Property(property: "nombre", type: "string", example: "Empresa S.A"),
                new OA\Property(property: "telefono", type: "string", example: "999888777"),
                new OA\Property(property: "direccion", type: "string", example: "Av. La Paz 123")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Cliente creado")]
    #[OA\Response(response: 422, description: "Error de validación")]
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20|unique:clientes,telefono',
            'direccion' => 'required|string|max:255'
        ]);

        $cliente = $this->clienteService->create($request->all());
        return response()->json($cliente, 201);
    }

    #[OA\Get(
        path: "/api/clientes/{id}",
        summary: "Obtener un cliente específico",
        security: [["sanctum" => []]],
        tags: ["Clientes"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Operación exitosa")]
    #[OA\Response(response: 404, description: "Cliente no encontrado")]
    public function show(string $id)
    {
        $cliente = $this->clienteService->findById($id);
        
        if (!$cliente) {
            return response()->json(['mensaje' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente);
    }

    #[OA\Put(
        path: "/api/clientes/{id}",
        summary: "Actualizar un cliente",
        security: [["sanctum" => []]],
        tags: ["Clientes"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "nombre", type: "string", example: "Empresa Nueva S.A."),
                new OA\Property(property: "telefono", type: "string", example: "999888777"),
                new OA\Property(property: "direccion", type: "string", example: "Av. Nueva 123")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Cliente actualizado")]
    #[OA\Response(response: 404, description: "Cliente no encontrado")]
    #[OA\Response(response: 422, description: "Error de validación")]
    public function update(Request $request, string $id)
    {
        $cliente = Cliente::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'telefono' => 'sometimes|string|max:20|unique:clientes,telefono,' . $cliente->id,
            'direccion' => 'sometimes|string|max:255'
        ]);

        $cliente = $this->clienteService->update($cliente, $request->all());
        return response()->json($cliente);
    }

    #[OA\Delete(
        path: "/api/clientes/{id}",
        summary: "Eliminar un cliente",
        security: [["sanctum" => []]],
        tags: ["Clientes"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Cliente eliminado")]
    #[OA\Response(response: 404, description: "Cliente no encontrado")]
    public function destroy(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        $this->clienteService->delete($cliente);

        return response()->json([
            "mensaje" => "Cliente eliminado correctamente"
        ]);
    }

    #[OA\Get(
        path: "/api/clientes/{id}/pedidos",
        summary: "Historial de pedidos del cliente",
        security: [["sanctum" => []]],
        tags: ["Clientes"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Lista de pedidos históricos")]
    public function pedidos(Request $request, string $id)
    {
        $cliente = $this->clienteService->findById($id);
        
        if (!$cliente) {
            return response()->json(['mensaje' => 'Cliente no encontrado'], 404);
        }
        
        $perPage = $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $pedidos = $this->clienteService->getPedidos($cliente, $perPage);

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
        path: "/api/clientes/telefono/{telefono}",
        summary: "Buscar cliente por teléfono",
        security: [["sanctum" => []]],
        tags: ["Clientes"]
    )]
    #[OA\Parameter(name: "telefono", in: "path", required: true, schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "Cliente encontrado")]
    #[OA\Response(response: 404, description: "Cliente no encontrado")]
    public function buscarPorTelefono(string $telefono)
    {
        $cliente = $this->clienteService->findByTelefono($telefono);
        
        if (!$cliente) {
            return response()->json(['mensaje' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente);
    }
}
