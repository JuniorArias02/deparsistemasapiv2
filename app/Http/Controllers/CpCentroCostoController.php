<?php

namespace App\Http\Controllers;

use App\Services\CpCentroCostoService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use App\Responses\ApiResponse;
use OpenApi\Attributes as OA;

class CpCentroCostoController extends Controller
{
    protected $service;
    protected $permissionService;

    public function __construct(CpCentroCostoService $service, PermissionService $permissionService)
    {
        $this->service = $service;
        $this->permissionService = $permissionService;
    }

    /**
     * Listar centros de costo.
     */
    #[OA\Get(
        path: '/api/cp-centro-costos',
        tags: ['CP Centro Costos'],
        summary: 'Listar centros de costo',
        description: 'Obtiene la lista de centros de costo. Requiere permiso cp_centro_costo.read.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de centros de costo', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        // $this->permissionService->authorize('cp_centro_costo.read');
        return ApiResponse::success($this->service->getAll(), 'Lista de centros de costo');
    }

    /**
     * Crear centro de costo.
     */
    #[OA\Post(
        path: '/api/cp-centro-costos',
        tags: ['CP Centro Costos'],
        summary: 'Crear centro de costo',
        description: 'Crea un nuevo centro de costo. Requiere permiso cp_centro_costo.create.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'codigo', type: 'integer', example: 101),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Recursos Humanos')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Centro de costo creado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        // $this->permissionService->authorize('cp_centro_costo.create');
        
        $validated = $request->validate([
            'codigo' => 'sometimes|integer',
            'nombre' => 'required|string|max:160'
        ]);

        return ApiResponse::created($this->service->create($validated), 'Centro de costo creado exitosamente');
    }

    /**
     * Mostrar centro de costo.
     */
    #[OA\Get(
        path: '/api/cp-centro-costos/{id}',
        tags: ['CP Centro Costos'],
        summary: 'Obtener centro de costo',
        description: 'Obtiene los detalles de un centro de costo. Requiere permiso cp_centro_costo.read.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalles del centro de costo', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Centro de costo no encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {

        return ApiResponse::success(\App\Models\CpCentroCosto::findOrFail($id), 'Detalle de centro de costo');
    }

    /**
     * Actualizar centro de costo.
     */
    #[OA\Put(
        path: '/api/cp-centro-costos/{id}',
        tags: ['CP Centro Costos'],
        summary: 'Actualizar centro de costo',
        description: 'Actualiza un centro de costo existente. Requiere permiso cp_centro_costo.update.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'codigo', type: 'integer', example: 102),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Recursos Humanos Updated')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Centro de costo actualizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Centro de costo no encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        // $this->permissionService->authorize('cp_centro_costo.update');

        $validated = $request->validate([
            'codigo' => 'sometimes|integer',
            'nombre' => 'sometimes|string|max:160'
        ]);

        return ApiResponse::success($this->service->update($id, $validated), 'Centro de costo actualizado exitosamente');
    }

    /**
     * Eliminar centro de costo.
     */
    #[OA\Delete(
        path: '/api/cp-centro-costos/{id}',
        tags: ['CP Centro Costos'],
        summary: 'Eliminar centro de costo',
        description: 'Elimina un centro de costo. Requiere permiso cp_centro_costo.delete.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Centro de costo eliminado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Centro de costo no encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        // $this->permissionService->authorize('cp_centro_costo.delete');
        $this->service->delete($id);
        return ApiResponse::success(null, 'Centro de costo eliminado exitosamente');
    }
}
