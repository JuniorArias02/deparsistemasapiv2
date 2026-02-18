<?php

namespace App\Http\Controllers;

use App\Services\AreaService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use App\Responses\ApiResponse;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Validator;

class AreaController extends Controller
{
    protected $service;
    protected $permissionService;

    public function __construct(AreaService $service, PermissionService $permissionService)
    {
        $this->service = $service;
        $this->permissionService = $permissionService;
    }

    /**
     * Listar areas.
     */
    #[OA\Get(
        path: '/api/areas',
        tags: ['Areas'],
        summary: 'Listar areas',
        description: 'Obtiene la lista de areas. Requiere permiso area.read.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de areas', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        // $this->permissionService->authorize('area.read');
        return ApiResponse::success($this->service->getAll(), 'Lista de areas');
    }

    /**
     * Crear area.
     */
    #[OA\Post(
        path: '/api/areas',
        tags: ['Areas'],
        summary: 'Crear area',
        description: 'Crea una nueva area. Requiere permiso area.create.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Recursos Humanos'),
                    new OA\Property(property: 'sede_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Area creada', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('area.crear');
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'sede_id' => 'required|exists:sedes,id'
        ]);

        return ApiResponse::created($this->service->create($validated), 'Area creada exitosamente');
    }

    /**
     * Mostrar area.
     */
    #[OA\Get(
        path: '/api/areas/{id}',
        tags: ['Areas'],
        summary: 'Obtener area',
        description: 'Obtiene los detalles de una area. Requiere permiso area.read.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalles del area', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Area no encontrada'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        return ApiResponse::success($this->service->update($id, []), 'Detalle de area');
    }

    /**
     * Actualizar area.
     */
    #[OA\Put(
        path: '/api/areas/{id}',
        tags: ['Areas'],
        summary: 'Actualizar area',
        description: 'Actualiza una area existente. Requiere permiso area.update.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Recursos Humanos Actualizado'),
                    new OA\Property(property: 'sede_id', type: 'integer', example: 2)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Area actualizada', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Area no encontrada'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('area.actualizar');

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'sede_id' => 'sometimes|exists:sedes,id'
        ]);

        return ApiResponse::success($this->service->update($id, $validated), 'Area actualizada exitosamente');
    }

    /**
     * Eliminar area.
     */
    #[OA\Delete(
        path: '/api/areas/{id}',
        tags: ['Areas'],
        summary: 'Eliminar area',
        description: 'Elimina una area. Requiere permiso area.delete.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Area eliminada', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Area no encontrada'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('area.eliminar');
        $this->service->delete($id);
        return ApiResponse::success(null, 'Area eliminada exitosamente');
    }
}
