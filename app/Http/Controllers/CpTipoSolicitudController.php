<?php

namespace App\Http\Controllers;

use App\Services\CpTipoSolicitudService;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CpTipoSolicitudController extends Controller
{
    public function __construct(
        protected CpTipoSolicitudService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/cp-tipos-solicitud',
        tags: ['CpTiposSolicitud'],
        summary: 'Listar tipos de solicitud',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de tipos', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        // $this->permissionService->authorize('cp_tipo_solicitud.read');
        $tipos = $this->service->getAll();
        return ApiResponse::success($tipos, 'Lista de tipos de solicitud');
    }

    #[OA\Post(
        path: '/api/cp-tipos-solicitud',
        tags: ['CpTiposSolicitud'],
        summary: 'Crear tipo de solicitud',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string'),
                    new OA\Property(property: 'descripcion', type: 'string'),
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
        // $this->permissionService->authorize('cp_tipo_solicitud.create');
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        try {
            $item = $this->service->create($request->all());
            return ApiResponse::success($item, 'Tipo de solicitud creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear tipo de solicitud: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/cp-tipos-solicitud/{id}',
        tags: ['CpTiposSolicitud'],
        summary: 'Obtener tipo de solicitud',
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
        // $this->permissionService->authorize('cp_tipo_solicitud.read');
        $item = $this->service->find($id);

        if (!$item) {
            return ApiResponse::error('Tipo de solicitud no encontrado', 404);
        }

        return ApiResponse::success($item, 'Detalle del tipo de solicitud');
    }

    #[OA\Put(
        path: '/api/cp-tipos-solicitud/{id}',
        tags: ['CpTiposSolicitud'],
        summary: 'Actualizar tipo de solicitud',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string'),
                    new OA\Property(property: 'descripcion', type: 'string'),
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
        // $this->permissionService->authorize('cp_tipo_solicitud.update');

        $item = $this->service->find($id);
        if (!$item) {
            return ApiResponse::error('Tipo de solicitud no encontrado', 404);
        }

        $validated = $request->validate([
            'nombre' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        try {
            $updated = $this->service->update($id, $request->all());
            return ApiResponse::success($updated, 'Tipo de solicitud actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar tipo de solicitud: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/cp-tipos-solicitud/{id}',
        tags: ['CpTiposSolicitud'],
        summary: 'Eliminar tipo de solicitud',
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
        // $this->permissionService->authorize('cp_tipo_solicitud.delete');

        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Tipo de solicitud eliminado exitosamente');
        }

        return ApiResponse::error('Tipo de solicitud no encontrado o no se pudo eliminar', 404);
    }
}
