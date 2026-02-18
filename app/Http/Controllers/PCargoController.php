<?php

namespace App\Http\Controllers;

use App\Services\PCargoService;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PCargoController extends Controller
{
    public function __construct(
        protected PCargoService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/p-cargos',
        tags: ['PCargos'],
        summary: 'Listar cargos',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de cargos', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        return ApiResponse::success($this->service->getAll(), 'Lista de cargos');
    }

    #[OA\Post(
        path: '/api/p-cargos',
        tags: ['PCargos'],
        summary: 'Crear cargo',
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
            new OA\Response(response: 201, description: 'Cargo creado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('p_cargo.crear');
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:60',
        ]);

        try {
            $cargo = $this->service->create($request->all());
            return ApiResponse::success($cargo, 'Cargo creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear cargo: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/p-cargos/{id}',
        tags: ['PCargos'],
        summary: 'Obtener cargo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle del cargo', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        $cargo = $this->service->find($id);

        if (!$cargo) {
            return ApiResponse::error('Cargo no encontrado', 404);
        }

        return ApiResponse::success($cargo, 'Detalle del cargo');
    }

    #[OA\Put(
        path: '/api/p-cargos/{id}',
        tags: ['PCargos'],
        summary: 'Actualizar cargo',
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
            new OA\Response(response: 200, description: 'Actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('p_cargo.actualizar');

        $cargo = $this->service->find($id);
        if (!$cargo) {
            return ApiResponse::error('Cargo no encontrado', 404);
        }

        $validated = $request->validate([
            'nombre' => 'nullable|string|max:60',
        ]);

        try {
            $updated = $this->service->update($id, $request->all());
            return ApiResponse::success($updated, 'Cargo actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar cargo: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/p-cargos/{id}',
        tags: ['PCargos'],
        summary: 'Eliminar cargo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('p_cargo.eliminar');

        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Cargo eliminado exitosamente');
        }

        return ApiResponse::error('Cargo no encontrado o no se pudo eliminar', 404);
    }
}
