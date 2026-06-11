<?php

namespace App\Modules\GestionSistemas\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Responses\ApiResponse;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Modules\GestionSistemas\Infrastructure\Repositories\PcLicenciaSoftwareRepository;

class PcLicenciasSoftwareController extends Controller
{
    private PcLicenciaSoftwareRepository $repository;

    public function __construct()
    {
        $this->repository = new PcLicenciaSoftwareRepository();
    }

    #[OA\Get(
        path: '/api/gestion-sistemas/pc-licencias-software/equipo/{equipo_id}',
        tags: ['PcLicenciasSoftware (DDD)'],
        summary: 'Obtener licencias por ID de Equipo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'equipo_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado')
        ]
    )]
    public function showByEquipo($equipo_id)
    {
        $item = $this->repository->getByEquipo($equipo_id);

        if (!$item) {
            return ApiResponse::error('Licencias no encontradas para este equipo', 404);
        }

        return ApiResponse::success($item, 'Licencias de software del equipo');
    }

    #[OA\Post(
        path: '/api/gestion-sistemas/pc-licencias-software',
        tags: ['PcLicenciasSoftware (DDD)'],
        summary: 'Crear o actualizar licencias',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'equipo_id', type: 'integer'),
                    new OA\Property(property: 'windows', type: 'string'),
                    new OA\Property(property: 'office', type: 'string'),
                    new OA\Property(property: 'nitro', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Procesado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse'))
        ]
    )]
    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'equipo_id' => 'required|integer|exists:pc_equipos,id',
            'windows' => 'nullable|string|max:20',
            'office' => 'nullable|string|max:20',
            'nitro' => 'nullable|string|max:20',
        ]);

        try {
            $existing = $this->repository->getByEquipo($validated['equipo_id']);
            
            if ($existing) {
                $item = $this->repository->update($existing->id, $validated);
                return ApiResponse::success($item, 'Licencias actualizadas exitosamente');
            } else {
                $item = $this->repository->create($validated);
                return ApiResponse::success($item, 'Licencias guardadas exitosamente', 201);
            }
        } catch (\Exception $e) {
            return ApiResponse::error('Error al procesar licencias: ' . $e->getMessage(), 500);
        }
    }
}
