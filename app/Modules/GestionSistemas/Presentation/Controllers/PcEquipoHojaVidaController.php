<?php

namespace App\Modules\GestionSistemas\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\GestionSistemas\Application\UseCases\EquiposComputo\ObtenerHojaVidaEquipoUseCase;
use App\Modules\GestionSistemas\Infrastructure\Repositories\PcEquipoRepository;
use App\Responses\ApiResponse;
use OpenApi\Attributes as OA;

class PcEquipoHojaVidaController extends Controller
{
    private ObtenerHojaVidaEquipoUseCase $obtenerHojaVidaUseCase;

    public function __construct()
    {
        $repository = new PcEquipoRepository();
        $this->obtenerHojaVidaUseCase = new ObtenerHojaVidaEquipoUseCase($repository);
    }

    #[OA\Get(
        path: '/api/gestion-sistemas/pc-equipos/{id}/hoja-vida',
        tags: ['PcEquipos (DDD)'],
        summary: 'Obtener hoja de vida completa de un equipo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Hoja de vida del equipo', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado')
        ]
    )]
    public function show(int $id)
    {
        try {
            $data = $this->obtenerHojaVidaUseCase->execute($id);

            if (!$data) {
                return ApiResponse::error('Equipo no encontrado', 404);
            }

            return ApiResponse::success($data, 'Hoja de vida del equipo (DDD)');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener hoja de vida: ' . $e->getMessage(), 500);
        }
    }
}
