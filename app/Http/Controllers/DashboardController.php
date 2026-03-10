<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: "/api/dashboard",
        summary: "Resumen de estadísticas",
        description: "Obtiene métricas de pedidos por estados",
        security: [["sanctum" => []]],
        tags: ["Dashboard"]
    )]
    #[OA\Response(
        response: 200,
        description: "Estadísticas generales",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "pedidos_pendientes", type: "integer", example: 5),
                new OA\Property(property: "pedidos_proceso", type: "integer", example: 2),
                new OA\Property(property: "pedidos_entregados", type: "integer", example: 10),
                new OA\Property(property: "total_pedidos", type: "integer", example: 17)
            ]
        )
    )]
    public function dashboard()
    {
        $pendientes = Pedido::where('estado', 'Pendiente')->count();
        $proceso = Pedido::where('estado', 'En proceso')->count();
        $entregados = Pedido::where('estado', 'Entregado')->count();
        $total = Pedido::count();

        return response()->json([
            "pedidos_pendientes" => $pendientes,
            "pedidos_proceso" => $proceso,
            "pedidos_entregados" => $entregados,
            "total_pedidos" => $total
        ]);
    }
}
