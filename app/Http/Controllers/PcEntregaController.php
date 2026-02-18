<?php

namespace App\Http\Controllers;

use App\Services\PcEntregaService;
use App\Responses\ApiResponse;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PcEntregaController extends Controller
{
    public function __construct(
        protected PcEntregaService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/pc-entregas',
        tags: ['PcEntregas'],
        summary: 'Listar entregas',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista obtenida', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $items = $this->service->getAll();
        return ApiResponse::success($items, 'Entregas listadas exitosamente');
    }

    #[OA\Post(
        path: '/api/pc-entregas',
        tags: ['PcEntregas'],
        summary: 'Crear entrega',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'equipo_id', type: 'integer'),
                    new OA\Property(property: 'funcionario_id', type: 'integer'),
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
        $this->permissionService->authorize('pc_entrega.listar');
        $validated = $request->validate([
            'equipo_id' => 'required|integer|exists:pc_equipos,id',
            'funcionario_id' => 'required|integer|exists:personal,id',
            'fecha_entrega' => 'nullable|date',
            'firma_entrega' => 'nullable|string',
            'firma_recibe' => 'nullable|string',
            'devuelto' => 'nullable|date',
            'estado' => 'nullable|in:entregado,devuelto',
        ]);

        try {
            $item = $this->service->create($validated);
            return ApiResponse::success($item, 'Entrega creada exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear entrega: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/pc-entregas/{id}',
        tags: ['PcEntregas'],
        summary: 'Obtener entrega',
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
            return ApiResponse::error('Entrega no encontrada', 404);
        }

        return ApiResponse::success($item, 'Detalle de la entrega');
    }

    #[OA\Put(
        path: '/api/pc-entregas/{id}',
        tags: ['PcEntregas'],
        summary: 'Actualizar entrega',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'estado', type: 'string'),
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
        $this->permissionService->authorize('pc_entrega.actualizar');
        $item = $this->service->find($id);
        if (!$item) {
            return ApiResponse::error('Entrega no encontrada', 404);
        }

        $validated = $request->validate([
            'equipo_id' => 'sometimes|integer|exists:pc_equipos,id',
            'funcionario_id' => 'sometimes|integer|exists:personal,id',
            'fecha_entrega' => 'nullable|date',
            'firma_entrega' => 'nullable|string',
            'firma_recibe' => 'nullable|string',
            'devuelto' => 'nullable|date',
            'estado' => 'nullable|in:entregado,devuelto',
        ]);

        try {
            $updated = $this->service->update($id, $validated);
            return ApiResponse::success($updated, 'Entrega actualizada exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar entrega: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/pc-entregas/{id}',
        tags: ['PcEntregas'],
        summary: 'Eliminar entrega',
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
        $this->permissionService->authorize('pc_entrega.eliminar');
        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Entrega eliminada exitosamente');
        }

        return ApiResponse::error('Entrega no encontrada o no se pudo eliminar', 404);
    }
}
