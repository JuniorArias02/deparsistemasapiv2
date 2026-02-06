<?php

namespace App\Http\Controllers;

use App\Services\PersonalService;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PersonalController extends Controller
{
    public function __construct(
        protected PersonalService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/personal',
        tags: ['Personal'],
        summary: 'Listar personal',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de personal', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $this->permissionService->authorize('personal.read');
        return ApiResponse::success($this->service->getAll(), 'Lista de personal');
    }

    #[OA\Post(
        path: '/api/personal',
        tags: ['Personal'],
        summary: 'Crear personal',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string'),
                    new OA\Property(property: 'cedula', type: 'string'),
                    new OA\Property(property: 'telefono', type: 'string'),
                    new OA\Property(property: 'cargo_id', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Personal creado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('personal.create');
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'cedula' => 'nullable|string|max:255|unique:personal,cedula',
            'telefono' => 'nullable|string|max:255',
            'cargo_id' => 'required|exists:p_cargo,id',
        ]);

        try {
            $personal = $this->service->create($request->all());
            return ApiResponse::success($personal, 'Personal creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear personal: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/personal/{id}',
        tags: ['Personal'],
        summary: 'Obtener personal',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle del personal', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        $this->permissionService->authorize('personal.read');
        $personal = $this->service->find($id);

        if (!$personal) {
            return ApiResponse::error('Personal no encontrado', 404);
        }

        return ApiResponse::success($personal, 'Detalle del personal');
    }

    #[OA\Put(
        path: '/api/personal/{id}',
        tags: ['Personal'],
        summary: 'Actualizar personal',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string'),
                    new OA\Property(property: 'cedula', type: 'string'),
                    new OA\Property(property: 'telefono', type: 'string'),
                    new OA\Property(property: 'cargo_id', type: 'integer'),
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
        $this->permissionService->authorize('personal.update');

        $personal = $this->service->find($id);
        if (!$personal) {
            return ApiResponse::error('Personal no encontrado', 404);
        }

        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'cedula' => 'nullable|string|max:255|unique:personal,cedula,' . $id,
            'telefono' => 'nullable|string|max:255',
            'cargo_id' => 'nullable|exists:p_cargo,id',
        ]);

        try {
            $updated = $this->service->update($id, $request->all());
            return ApiResponse::success($updated, 'Personal actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar personal: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/personal/{id}',
        tags: ['Personal'],
        summary: 'Eliminar personal',
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
        $this->permissionService->authorize('personal.delete');

        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Personal eliminado exitosamente');
        }

        return ApiResponse::error('Personal no encontrado o no se pudo eliminar', 404);
    }
}
