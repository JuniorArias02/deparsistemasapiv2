<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatsService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DashboardStatsController extends Controller
{
    public function __construct(
        protected DashboardStatsService $service
    ) {}

    #[OA\Get(
        path: '/api/dashboard/stats',
        tags: ['Dashboard'],
        summary: 'Obtener estadísticas del dashboard',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'query',
                description: 'Tipo de dashboard (sistemas, compras, admin)',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['sistemas', 'compras', 'admin'])
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Estadísticas obtenidas', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index(Request $request)
    {
        $type = $request->query('type', 'admin');

        $stats = [];
        switch ($type) {
            case 'sistemas':
                $stats = $this->service->getSistemasStats();
                break;
            case 'compras':
                $stats = $this->service->getComprasStats();
                break;
            case 'administrador web':
            default:
                $stats = $this->service->getAdminStats();
                break;
        }

        return ApiResponse::success($stats, 'Estadísticas del dashboard obtenidas exitosamente');
    }
}
