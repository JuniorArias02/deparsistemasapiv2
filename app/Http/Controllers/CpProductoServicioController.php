<?php

namespace App\Http\Controllers;

use App\Services\CpProductoServicioService;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CpProductoServicioController extends Controller
{
    public function __construct(
        protected CpProductoServicioService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/cp-productos-servicios',
        tags: ['CpProductosServicios'],
        summary: 'Listar productos servicios',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de productos servicios', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $this->permissionService->authorize('cp_producto_servicio.read');
        return ApiResponse::success($this->service->getAll(), 'Lista de productos servicios');
    }

    #[OA\Post(
        path: '/api/cp-productos-servicios',
        tags: ['CpProductosServicios'],
        summary: 'Crear producto servicio',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'codigo_producto', type: 'string'),
                    new OA\Property(property: 'nombre', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Producto servicio creado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('cp_producto_servicio.create');
        
        $validated = $request->validate([
            'codigo_producto' => 'required|string|max:50',
            'nombre' => 'required|string|max:255',
        ]);

        try {
            $item = $this->service->create($request->all());
            return ApiResponse::success($item, 'Producto servicio creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear producto servicio: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/cp-productos-servicios/{id}',
        tags: ['CpProductosServicios'],
        summary: 'Obtener producto servicio',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle del producto servicio', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        $this->permissionService->authorize('cp_producto_servicio.read');
        $item = $this->service->find($id);

        if (!$item) {
            return ApiResponse::error('Producto servicio no encontrado', 404);
        }

        return ApiResponse::success($item, 'Detalle del producto servicio');
    }

    #[OA\Put(
        path: '/api/cp-productos-servicios/{id}',
        tags: ['CpProductosServicios'],
        summary: 'Actualizar producto servicio',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'codigo_producto', type: 'string'),
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
        $this->permissionService->authorize('cp_producto_servicio.update');

        $item = $this->service->find($id);
        if (!$item) {
            return ApiResponse::error('Producto servicio no encontrado', 404);
        }

        $validated = $request->validate([
            'codigo_producto' => 'nullable|string|max:50',
            'nombre' => 'nullable|string|max:255',
        ]);

        try {
            $updated = $this->service->update($id, $request->all());
            return ApiResponse::success($updated, 'Producto servicio actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar producto servicio: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/cp-productos-servicios/{id}',
        tags: ['CpProductosServicios'],
        summary: 'Eliminar producto servicio',
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
        $this->permissionService->authorize('cp_producto_servicio.delete');

        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Producto servicio eliminado exitosamente');
        }

        return ApiResponse::error('Producto servicio no encontrado o no se pudo eliminar', 404);
    }
}
