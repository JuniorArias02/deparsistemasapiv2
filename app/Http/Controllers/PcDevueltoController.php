<?php

namespace App\Http\Controllers;

use App\Services\PcDevueltoService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PcDevueltoController extends Controller
{
    public function __construct(
        protected PcDevueltoService $service
    ) {}

    #[OA\Get(
        path: '/api/pc-devueltos',
        tags: ['PcDevueltos'],
        summary: 'Listar devoluciones',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista obtenida', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $items = $this->service->getAll();
        return ApiResponse::success($items, 'Devoluciones listadas exitosamente');
    }

    #[OA\Post(
        path: '/api/pc-devueltos',
        tags: ['PcDevueltos'],
        summary: 'Registrar devolución',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'entrega_id', type: 'integer'),
                    new OA\Property(property: 'firma_entrega', type: 'string'),
                    new OA\Property(property: 'firma_recibe', type: 'string'),
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
        $validated = $request->validate([
            'entrega_id' => 'required|integer|exists:pc_entregas,id|unique:pc_devuelto,entrega_id',
            'firma_entrega' => 'required|string',
            'firma_recibe' => 'required|string',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $item = $this->service->create($validated);
            return ApiResponse::success($item, 'Devolución registrada exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al registrar devolución: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/pc-devueltos/{id}',
        tags: ['PcDevueltos'],
        summary: 'Obtener devolución',
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
            return ApiResponse::error('Devolución no encontrada', 404);
        }

        return ApiResponse::success($item, 'Detalle de la devolución');
    }

    #[OA\Put(
        path: '/api/pc-devueltos/{id}',
        tags: ['PcDevueltos'],
        summary: 'Actualizar devolución',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'observaciones', type: 'string'),
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
        $item = $this->service->find($id);
        if (!$item) {
            return ApiResponse::error('Devolución no encontrada', 404);
        }

        $validated = $request->validate([
            'entrega_id' => 'sometimes|integer|exists:pc_entregas,id|unique:pc_devuelto,entrega_id,' . $id,
            'firma_entrega' => 'sometimes|string',
            'firma_recibe' => 'sometimes|string',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $updated = $this->service->update($id, $validated);
            return ApiResponse::success($updated, 'Devolución actualizada exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar devolución: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/pc-devueltos/{id}',
        tags: ['PcDevueltos'],
        summary: 'Eliminar devolución',
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
            return ApiResponse::success(null, 'Devolución eliminada exitosamente');
        }

        return ApiResponse::error('Devolución no encontrada o no se pudo eliminar', 404);
    }
}
