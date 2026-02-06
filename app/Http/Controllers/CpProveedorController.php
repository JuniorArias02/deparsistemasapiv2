<?php

namespace App\Http\Controllers;

use App\Services\CpProveedorService;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CpProveedorController extends Controller
{
    public function __construct(
        protected CpProveedorService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/cp-proveedores',
        tags: ['CpProveedores'],
        summary: 'Listar proveedores',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de proveedores', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        // $this->permissionService->authorize('cp_proveedor.read');
        return ApiResponse::success($this->service->getAll(), 'Lista de proveedores');
    }

    #[OA\Post(
        path: '/api/cp-proveedores',
        tags: ['CpProveedores'],
        summary: 'Crear proveedor',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string'),
                    new OA\Property(property: 'nit', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Proveedor creado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('cp_proveedor.create');
        
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'nit' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|string|email|max:100',
            'direccion' => 'nullable|string|max:255',
        ]);

        try {
            $proveedor = $this->service->create($request->all());
            return ApiResponse::success($proveedor, 'Proveedor creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear proveedor: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/cp-proveedores/{id}',
        tags: ['CpProveedores'],
        summary: 'Obtener proveedor',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle del proveedor', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        $this->permissionService->authorize('cp_proveedor.read');
        $proveedor = $this->service->find($id);

        if (!$proveedor) {
            return ApiResponse::error('Proveedor no encontrado', 404);
        }

        return ApiResponse::success($proveedor, 'Detalle del proveedor');
    }

    #[OA\Put(
        path: '/api/cp-proveedores/{id}',
        tags: ['CpProveedores'],
        summary: 'Actualizar proveedor',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string'),
                    new OA\Property(property: 'nit', type: 'string'),
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
        $this->permissionService->authorize('cp_proveedor.update');

        $proveedor = $this->service->find($id);
        if (!$proveedor) {
            return ApiResponse::error('Proveedor no encontrado', 404);
        }

        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'nit' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|string|email|max:100',
            'direccion' => 'nullable|string|max:255',
        ]);

        try {
            $updated = $this->service->update($id, $request->all());
            return ApiResponse::success($updated, 'Proveedor actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar proveedor: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/cp-proveedores/{id}',
        tags: ['CpProveedores'],
        summary: 'Eliminar proveedor',
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
        $this->permissionService->authorize('cp_proveedor.delete');

        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Proveedor eliminado exitosamente');
        }

        return ApiResponse::error('Proveedor no encontrado o no se pudo eliminar', 404);
    }
}
