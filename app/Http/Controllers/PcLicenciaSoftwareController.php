<?php

namespace App\Http\Controllers;

use App\Services\PcLicenciaSoftwareService;
use App\Responses\ApiResponse;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PcLicenciaSoftwareController extends Controller
{
    public function __construct(
        protected PcLicenciaSoftwareService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/pc-licencias-software',
        tags: ['PcLicenciasSoftware'],
        summary: 'Listar licencias',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista obtenida', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $items = $this->service->getAll();
        return ApiResponse::success($items, 'Licencias listadas exitosamente');
    }

    #[OA\Post(
        path: '/api/pc-licencias-software',
        tags: ['PcLicenciasSoftware'],
        summary: 'Registrar licencia',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'equipo_id', type: 'integer'),
                    new OA\Property(property: 'windows', type: 'string'),
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
        $this->permissionService->authorize("pc_licencia_software.crear");
        $validated = $request->validate([
            'equipo_id' => 'required|integer|exists:pc_equipos,id|unique:pc_licencias_software,equipo_id',
            'windows' => 'nullable|string|max:10',
            'office' => 'nullable|string|max:10',
            'nitro' => 'nullable|string|max:10',
        ]);

        try {
            $item = $this->service->create($validated);
            return ApiResponse::success($item, 'Licencia registrada exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al registrar licencia: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/pc-licencias-software/{id}',
        tags: ['PcLicenciasSoftware'],
        summary: 'Obtener licencia',
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
            return ApiResponse::error('Licencia no encontrada', 404);
        }

        return ApiResponse::success($item, 'Detalle de la licencia');
    }

    #[OA\Get(
        path: '/api/pc-licencias-software/equipo/{equipo_id}',
        tags: ['PcLicenciasSoftware'],
        summary: 'Obtener licencia por equipo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'equipo_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function showByEquipo($equipo_id)
    {
        $item = $this->service->getByEquipo($equipo_id);

        if (!$item) {
            return ApiResponse::error('Licencia no encontrada para este equipo', 404);
        }

        return ApiResponse::success($item, 'Detalle de la licencia del equipo');
    }

    #[OA\Put(
        path: '/api/pc-licencias-software/{id}',
        tags: ['PcLicenciasSoftware'],
        summary: 'Actualizar licencia',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'windows', type: 'string'),
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
        $this->permissionService->authorize("pc_licencia_software.actualizar");
        $item = $this->service->find($id);
        if (!$item) {
            return ApiResponse::error('Licencia no encontrada', 404);
        }

        $validated = $request->validate([
            'equipo_id' => 'sometimes|integer|exists:pc_equipos,id|unique:pc_licencias_software,equipo_id,' . $id,
            'windows' => 'nullable|string|max:10',
            'office' => 'nullable|string|max:10',
            'nitro' => 'nullable|string|max:10',
        ]);

        try {
            $updated = $this->service->update($id, $validated);
            return ApiResponse::success($updated, 'Licencia actualizada exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/pc-licencias-software/{id}',
        tags: ['PcLicenciasSoftware'],
        summary: 'Eliminar licencia',
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
        $this->permissionService->authorize("pc_licencia_software.eliminar");
        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Licencia eliminada exitosamente');
        }

        return ApiResponse::error('Licencia no encontrada o no se pudo eliminar', 404);
    }
}
