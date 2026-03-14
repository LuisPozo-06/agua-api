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
    protected \App\Services\DashboardService $dashboardService;

    public function __construct(\App\Services\DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

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
                new OA\Property(property: "pagos_pendientes", type: "integer", example: 3),
                new OA\Property(property: "total_pedidos", type: "integer", example: 17)
            ]
        )
    )]
    public function dashboard()
    {
        return response()->json($this->dashboardService->getMetrics());
    }
}
