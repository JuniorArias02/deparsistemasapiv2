<?php

namespace App\Http\Controllers;

use App\Services\PcPerifericoEntregadoService;
use App\Responses\ApiResponse;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PcPerifericoEntregadoController extends Controller
{
    public function __construct(
        protected PcPerifericoEntregadoService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/pc-perifericos-entregados',
        tags: ['PcPerifericosEntregados'],
        summary: 'Listar periféricos entregados',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista obtenida', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $items = $this->service->getAll();
        return ApiResponse::success($items, 'Listado exitoso');
    }

    #[OA\Post(
        path: '/api/pc-perifericos-entregados',
        tags: ['PcPerifericosEntregados'],
        summary: 'Registrar periférico en entrega',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'entrega_id', type: 'integer'),
                    new OA\Property(property: 'inventario_id', type: 'integer'),
                    new OA\Property(property: 'cantidad', type: 'integer'),
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
        $this->permissionService->authorize("pc_periferico_entregado.crud");
        $validated = $request->validate([
            'entrega_id' => 'required|integer|exists:pc_entregas,id',
            'inventario_id' => 'required|integer|exists:inventario,id',
            'cantidad' => 'nullable|integer',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $item = $this->service->create($validated);
            return ApiResponse::success($item, 'Periférico registrado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al registrar periférico: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/pc-perifericos-entregados/{id}',
        tags: ['PcPerifericosEntregados'],
        summary: 'Obtener periférico entregado',
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
            return ApiResponse::error('Registro no encontrado', 404);
        }

        return ApiResponse::success($item, 'Detalle obtenido');
    }

    #[OA\Get(
        path: '/api/pc-perifericos-entregados/entrega/{entrega_id}',
        tags: ['PcPerifericosEntregados'],
        summary: 'Listar por Entrega',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'entrega_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Lista obtenida', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado')
        ]
    )]
    public function showByEntrega($entrega_id)
    {
        $items = $this->service->getByEntrega($entrega_id);
        return ApiResponse::success($items, 'Listado por entrega');
    }

    #[OA\Put(
        path: '/api/pc-perifericos-entregados/{id}',
        tags: ['PcPerifericosEntregados'],
        summary: 'Actualizar registro',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'cantidad', type: 'integer'),
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
        $this->permissionService->authorize("pc_periferico_entregado.crud");
        $item = $this->service->find($id);
        if (!$item) {
            return ApiResponse::error('Registro no encontrado', 404);
        }

        $validated = $request->validate([
            'entrega_id' => 'sometimes|integer|exists:pc_entregas,id',
            'inventario_id' => 'sometimes|integer|exists:inventario,id',
            'cantidad' => 'nullable|integer',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $updated = $this->service->update($id, $validated);
            return ApiResponse::success($updated, 'Registro actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/pc-perifericos-entregados/{id}',
        tags: ['PcPerifericosEntregados'],
        summary: 'Eliminar registro',
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
        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Registro eliminado exitosamente');
        }

        return ApiResponse::error('Registro no encontrado o no se pudo eliminar', 404);
    }
}
