<?php

namespace App\Http\Controllers;

use App\Services\CpProductoService;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CpProductoController extends Controller
{
    public function __construct(
        protected CpProductoService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/cp-productos',
        tags: ['CpProductos'],
        summary: 'Listar productos',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de productos', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        return ApiResponse::success($this->service->getAll(), 'Lista de productos');
    }

    #[OA\Post(
        path: '/api/cp-productos',
        tags: ['CpProductos'],
        summary: 'Crear producto',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'codigo', type: 'string'),
                    new OA\Property(property: 'nombre', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Producto creado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido'),
            new OA\Response(response: 500, description: 'Error del servidor')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('cp_producto.crear');

        $validated = $request->validate([
            'codigo' => 'nullable|string|max:255|unique:cp_productos,codigo',
            'nombre' => 'nullable|string|max:255',
        ]);

        try {
            $producto = $this->service->create($request->all());
            return ApiResponse::success($producto, 'Producto creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear producto: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/cp-productos/{id}',
        tags: ['CpProductos'],
        summary: 'Obtener producto',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle del producto', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Producto no encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        $producto = $this->service->find($id);

        if (!$producto) {
            return ApiResponse::error('Producto no encontrado', 404);
        }

        return ApiResponse::success($producto, 'Detalle del producto');
    }

    #[OA\Put(
        path: '/api/cp-productos/{id}',
        tags: ['CpProductos'],
        summary: 'Actualizar producto',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'codigo', type: 'string'),
                    new OA\Property(property: 'nombre', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Producto actualizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Producto no encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        
        $this->permissionService->authorize('cp_producto.actualizar');

        $producto = $this->service->find($id);
        if (!$producto) {
            return ApiResponse::error('Producto no encontrado', 404);
        }

        $validated = $request->validate([
            'codigo' => 'nullable|string|max:255|unique:cp_productos,codigo,' . $id,
            'nombre' => 'nullable|string|max:255',
        ]);

        try {
            $updated = $this->service->update($id, $request->all());
            return ApiResponse::success($updated, 'Producto actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar producto: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/cp-productos/{id}',
        tags: ['CpProductos'],
        summary: 'Eliminar producto',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Producto eliminado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Producto no encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('cp_producto.eliminar');

        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Producto eliminado exitosamente');
        }

        return ApiResponse::error('Producto no encontrado o no se pudo eliminar', 404);
    }
}
