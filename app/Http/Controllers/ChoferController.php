<?php

namespace App\Http\Controllers;

use App\Models\Chofer;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ChoferController extends Controller
{
    #[OA\Get(
        path: "/api/choferes",
        summary: "Listar todos los choferes",
        security: [["sanctum" => []]],
        tags: ["Choferes"]
    )]
    #[OA\Response(response: 200, description: "Lista de choferes")]
    public function index()
    {
        return response()->json(Chofer::all());
    }

    #[OA\Post(
        path: "/api/choferes",
        summary: "Crear un nuevo chofer",
        security: [["sanctum" => []]],
        tags: ["Choferes"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["nombres_completos"],
            properties: [
                new OA\Property(property: "nombres_completos", type: "string", example: "Juan Perez"),
                new OA\Property(property: "telefono", type: "string", example: "987654321"),
                new OA\Property(property: "is_active", type: "boolean", example: true)
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Chofer creado")]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres_completos' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
        }

        $chofer = Chofer::create($validated);
        return response()->json($chofer, 201);
    }

    #[OA\Get(
        path: "/api/choferes/{chofer}",
        summary: "Ver detalle de chofer",
        security: [["sanctum" => []]],
        tags: ["Choferes"]
    )]
    #[OA\Parameter(name: "chofer", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Detalles del chofer")]
    public function show(Chofer $chofer)
    {
        return response()->json($chofer);
    }

    #[OA\Put(
        path: "/api/choferes/{chofer}",
        summary: "Actualizar chofer",
        security: [["sanctum" => []]],
        tags: ["Choferes"]
    )]
    #[OA\Parameter(name: "chofer", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "nombres_completos", type: "string", example: "Juan Perez Mod"),
                new OA\Property(property: "telefono", type: "string", example: "987654321"),
                new OA\Property(property: "is_active", type: "boolean", example: false),
                new OA\Property(property: "estado_asignacion", type: "string", example: "disponible")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Chofer actualizado")]
    public function update(Request $request, Chofer $chofer)
    {
        $validated = $request->validate([
            'nombres_completos' => 'sometimes|required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'estado_asignacion' => 'in:disponible,ocupado'
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
        }

        $chofer->update($validated);
        return response()->json($chofer);
    }

    #[OA\Delete(
        path: "/api/choferes/{chofer}",
        summary: "Eliminar chofer",
        security: [["sanctum" => []]],
        tags: ["Choferes"]
    )]
    #[OA\Parameter(name: "chofer", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 204, description: "Chofer eliminado")]
    public function destroy(Chofer $chofer)
    {
        $chofer->delete();
        return response()->json(null, 204);
    }
}
