<?php

namespace App\Http\Controllers;

use App\Services\PcConfigCronogramaService;
use App\Responses\ApiResponse;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PcConfigCronogramaController extends Controller
{
    public function __construct(
        protected PcConfigCronogramaService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/pc-config-cronograma',
        tags: ['PcConfigCronograma'],
        summary: 'Listar configuraciones de cronograma',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista obtenida', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $items = $this->service->getAll();
        return ApiResponse::success($items, 'Configuraciones listadas exitosamente');
    }

    #[OA\Post(
        path: '/api/pc-config-cronograma',
        tags: ['PcConfigCronograma'],
        summary: 'Registrar configuración de cronograma',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'dias_cumplimiento', type: 'integer'),
                    new OA\Property(property: 'meses_cumplimiento', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dias_cumplimiento' => 'required|integer',
            'meses_cumplimiento' => 'required|integer',
        ]);

        try {
            $item = $this->service->create($validated);
            return ApiResponse::success($item, 'Configuración creada exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear configuración: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/pc-config-cronograma/{id}',
        tags: ['PcConfigCronograma'],
        summary: 'Obtener configuración de cronograma',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        $item = $this->service->find($id);

        if (!$item) {
            return ApiResponse::error('Configuración no encontrada', 404);
        }

        return ApiResponse::success($item, 'Detalle de la configuración');
    }

    #[OA\Put(
        path: '/api/pc-config-cronograma/{id}',
        tags: ['PcConfigCronograma'],
        summary: 'Actualizar configuración de cronograma',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'dias_cumplimiento', type: 'integer'),
                    new OA\Property(property: 'meses_cumplimiento', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Actualizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        $item = $this->service->find($id);
        if (!$item) {
            return ApiResponse::error('Configuración no encontrada', 404);
        }

        $validated = $request->validate([
            'dias_cumplimiento' => 'sometimes|integer',
            'meses_cumplimiento' => 'sometimes|integer',
        ]);

        try {
            $updated = $this->service->update($id, $validated);
            return ApiResponse::success($updated, 'Configuración actualizada exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar configuración: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/pc-config-cronograma/{id}',
        tags: ['PcConfigCronograma'],
        summary: 'Eliminar configuración de cronograma',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Eliminado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Configuración eliminada exitosamente');
        }

        return ApiResponse::error('Configuración no encontrada o no se pudo eliminar', 404);
    }
}
