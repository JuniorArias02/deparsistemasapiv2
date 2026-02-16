<?php

namespace App\Http\Controllers;

use App\Services\CpDependenciaService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use App\Responses\ApiResponse;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Validator;

class CpDependenciaController extends Controller
{
    protected $service;
    protected $permissionService;

    public function __construct(CpDependenciaService $service, PermissionService $permissionService)
    {
        $this->service = $service;
        $this->permissionService = $permissionService;
    }

    /**
     * Listar dependencias.
     */
    #[OA\Get(
        path: '/api/cp-dependencias',
        tags: ['CP Dependencias'],
        summary: 'Listar dependencias',
        description: 'Obtiene la lista de dependencias. Requiere permiso cp_dependencia.read.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de dependencias', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index(Request $request)
    {
        // $this->permissionService->authorize('cp_dependencia.read');
        return ApiResponse::success($this->service->getAll($request->get('sede_id')), 'Lista de dependencias');
    }

    /**
     * Crear dependencia.
     */
    #[OA\Post(
        path: '/api/cp-dependencias',
        tags: ['CP Dependencias'],
        summary: 'Crear dependencia',
        description: 'Crea una nueva dependencia. Requiere permiso cp_dependencia.create.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'codigo', type: 'integer', example: 123),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Dirección General'),
                    new OA\Property(property: 'sede_id', type: 'integer', example: 1)
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
        // $this->permissionService->authorize('cp_dependencia.create');
        
        $validated = $request->validate([
            'codigo' => 'sometimes|integer',
            'nombre' => 'required|string|max:160',
            'sede_id' => 'required|exists:sedes,id'
        ]);

        return ApiResponse::created($this->service->create($validated), 'Dependencia creada exitosamente');
    }

    /**
     * Mostrar dependencia.
     */
    #[OA\Get(
        path: '/api/cp-dependencias/{id}',
        tags: ['CP Dependencias'],
        summary: 'Obtener dependencia',
        description: 'Obtiene los detalles de una dependencia. Requiere permiso cp_dependencia.read.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalles de la dependencia', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Dependencia no encontrada'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        // $this->permissionService->authorize('cp_dependencia.read');
        return ApiResponse::success($this->service->update($id, []), 'Detalle de dependencia');
    }

    /**
     * Actualizar dependencia.
     */
    #[OA\Put(
        path: '/api/cp-dependencias/{id}',
        tags: ['CP Dependencias'],
        summary: 'Actualizar dependencia',
        description: 'Actualiza una dependencia existente. Requiere permiso cp_dependencia.update.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'codigo', type: 'integer', example: 124),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Dirección General Actualizada'),
                    new OA\Property(property: 'sede_id', type: 'integer', example: 2)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Dependencia actualizada', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Dependencia no encontrada'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        // $this->permissionService->authorize('cp_dependencia.update');

        $validated = $request->validate([
            'codigo' => 'sometimes|integer',
            'nombre' => 'sometimes|string|max:160',
            'sede_id' => 'sometimes|exists:sedes,id'
        ]);

        return ApiResponse::success($this->service->update($id, $validated), 'Dependencia actualizada exitosamente');
    }

    /**
     * Eliminar dependencia.
     */
    #[OA\Delete(
        path: '/api/cp-dependencias/{id}',
        tags: ['CP Dependencias'],
        summary: 'Eliminar dependencia',
        description: 'Elimina una dependencia. Requiere permiso cp_dependencia.delete.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Dependencia eliminada', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Dependencia no encontrada'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        // $this->permissionService->authorize('cp_dependencia.delete');
        $this->service->delete($id);
        return ApiResponse::success(null, 'Dependencia eliminada exitosamente');
    }
}
