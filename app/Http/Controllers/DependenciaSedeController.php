<?php

namespace App\Http\Controllers;

use App\Services\DependenciaSedeService;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DependenciaSedeController extends Controller
{
    public function __construct(
        protected DependenciaSedeService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/dependencias-sedes',
        tags: ['DependenciasSedes'],
        summary: 'Listar dependencias de sedes',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de dependencias', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index(Request $request)
    {
        // $this->permissionService->authorize('dependencia_sede.read');
        return ApiResponse::success($this->service->getAll($request->get('sede_id')), 'Lista de dependencias de sedes');
    }

    #[OA\Post(
        path: '/api/dependencias-sedes',
        tags: ['DependenciasSedes'],
        summary: 'Crear dependencia de sede',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'sede_id', type: 'integer'),
                    new OA\Property(property: 'nombre', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Dependencia creada', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        // $this->permissionService->authorize('dependencia_sede.create');
        
        $validated = $request->validate([
            'sede_id' => 'required|exists:sedes,id',
            'nombre' => 'required|string|max:255',
        ]);

        try {
            $item = $this->service->create($request->all());
            return ApiResponse::success($item, 'Dependencia creada exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear dependencia: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/dependencias-sedes/{id}',
        tags: ['DependenciasSedes'],
        summary: 'Obtener dependencia de sede',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle de la dependencia', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        // $this->permissionService->authorize('dependencia_sede.read');
        $item = $this->service->find($id);

        if (!$item) {
            return ApiResponse::error('Dependencia no encontrada', 404);
        }

        return ApiResponse::success($item, 'Detalle de la dependencia');
    }

    #[OA\Put(
        path: '/api/dependencias-sedes/{id}',
        tags: ['DependenciasSedes'],
        summary: 'Actualizar dependencia de sede',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'sede_id', type: 'integer'),
                    new OA\Property(property: 'nombre', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Actualizada exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        // $this->permissionService->authorize('dependencia_sede.update');

        $item = $this->service->find($id);
        if (!$item) {
            return ApiResponse::error('Dependencia no encontrada', 404);
        }

        $validated = $request->validate([
            'sede_id' => 'nullable|exists:sedes,id',
            'nombre' => 'nullable|string|max:255',
        ]);

        try {
            $updated = $this->service->update($id, $request->all());
            return ApiResponse::success($updated, 'Dependencia actualizada exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar dependencia: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/dependencias-sedes/{id}',
        tags: ['DependenciasSedes'],
        summary: 'Eliminar dependencia de sede',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Eliminada exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        // $this->permissionService->authorize('dependencia_sede.delete');

        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Dependencia eliminada exitosamente');
        }

        return ApiResponse::error('Dependencia no encontrada o no se pudo eliminar', 404);
    }
}
