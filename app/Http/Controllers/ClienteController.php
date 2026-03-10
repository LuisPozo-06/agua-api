<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ClienteController extends Controller
{
    #[OA\Get(
        path: "/api/clientes",
        summary: "Listar todos los clientes",
        security: [["sanctum" => []]],
        tags: ["Clientes"]
    )]
    #[OA\Response(response: 200, description: "Operación exitosa")]
    public function index()
    {
        $clientes = Cliente::all();
        return response()->json($clientes);
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

        $cliente = Cliente::create([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion
        ]);

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
        $cliente = Cliente::findOrFail($id);
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

        $cliente->update($request->all());
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
        $cliente->delete();

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
    #[OA\Response(response: 200, description: "Lista de pedidos históricos")]
    public function pedidos(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        $pedidos = $cliente->pedidos()->orderBy('fecha_pedido', 'desc')->get();

        return response()->json($pedidos);
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
        $cliente = Cliente::where('telefono', $telefono)->firstOrFail();
        return response()->json($cliente);
    }
}
