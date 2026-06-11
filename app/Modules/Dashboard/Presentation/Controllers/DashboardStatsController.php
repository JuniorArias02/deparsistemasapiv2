<?php

namespace App\Modules\Dashboard\Presentation\Controllers;

use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

use App\Modules\Dashboard\Application\UseCases\ObtenerEstadisticasUseCase;

class DashboardStatsController extends Controller
{
    public function __construct(
        protected ObtenerEstadisticasUseCase $obtenerEstadisticasUseCase
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
        
        $stats = $this->obtenerEstadisticasUseCase->execute($type);

        return ApiResponse::success($stats, 'Estadísticas del dashboard obtenidas exitosamente');
    }
}
