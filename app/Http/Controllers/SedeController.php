<?php

namespace App\Http\Controllers;

use App\Services\SedeService;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SedeController extends Controller
{
    public function __construct(
        protected SedeService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/sedes',
        tags: ['Sedes'],
        summary: 'Listar sedes',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de sedes', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        return ApiResponse::success($this->service->getAll(), 'Lista de sedes');
    }

    #[OA\Post(
        path: '/api/sedes',
        tags: ['Sedes'],
        summary: 'Crear sede',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Sede creada', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('sede.create');
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        try {
            $sede = $this->service->create($request->all());
            return ApiResponse::success($sede, 'Sede creada exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear sede: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/sedes/{id}',
        tags: ['Sedes'],
        summary: 'Obtener sede',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle de la sede', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        $this->permissionService->authorize('sede.read');
        $sede = $this->service->find($id);

        if (!$sede) {
            return ApiResponse::error('Sede no encontrada', 404);
        }

        return ApiResponse::success($sede, 'Detalle de la sede');
    }

    #[OA\Put(
        path: '/api/sedes/{id}',
        tags: ['Sedes'],
        summary: 'Actualizar sede',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
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
        $this->permissionService->authorize('sede.update');

        $sede = $this->service->find($id);
        if (!$sede) {
            return ApiResponse::error('Sede no encontrada', 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        try {
            $updated = $this->service->update($id, $request->all());
            return ApiResponse::success($updated, 'Sede actualizada exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar sede: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/sedes/{id}',
        tags: ['Sedes'],
        summary: 'Eliminar sede',
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
        $this->permissionService->authorize('sede.delete');

        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Sede eliminada exitosamente');
        }

        return ApiResponse::error('Sede no encontrada o no se pudo eliminar', 404);
    }
}
