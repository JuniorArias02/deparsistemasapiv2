<?php

namespace App\Modules\GestionCompras\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\GestionCompras\Application\UseCases\Producto\ListarProductoUseCase;
use App\Modules\GestionCompras\Application\UseCases\Producto\CrearProductoUseCase;
use App\Modules\GestionCompras\Application\UseCases\Producto\ObtenerProductoUseCase;
use App\Modules\GestionCompras\Application\UseCases\Producto\ActualizarProductoUseCase;
use App\Modules\GestionCompras\Application\UseCases\Producto\EliminarProductoUseCase;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CpProductoController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected ListarProductoUseCase $listarUseCase,
        protected CrearProductoUseCase $crearUseCase,
        protected ObtenerProductoUseCase $obtenerUseCase,
        protected ActualizarProductoUseCase $actualizarUseCase,
        protected EliminarProductoUseCase $eliminarUseCase
    ) {}

    #[OA\Get(
        path: '/api/gestion-compras/cp-productos',
        tags: ['CpProductos'],
        summary: 'Listar producto',
        security: [['bearerAuth' => []]]
    )]
    public function index()
    {
        $items = $this->listarUseCase->execute();
        return ApiResponse::success($items, 'Lista de producto');
    }

    #[OA\Post(
        path: '/api/gestion-compras/cp-productos',
        tags: ['CpProductos'],
        summary: 'Crear producto',
        security: [['bearerAuth' => []]]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('cp_producto.crear');
        try {
            $item = $this->crearUseCase->execute($request->all());
            return ApiResponse::success($item, ucfirst('producto') . ' creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/gestion-compras/cp-productos/{id}',
        tags: ['CpProductos'],
        summary: 'Obtener producto',
        security: [['bearerAuth' => []]]
    )]
    public function show($id)
    {
        $item = $this->obtenerUseCase->execute($id);
        if (!$item) {
            return ApiResponse::error(ucfirst('producto') . ' no encontrado', 404);
        }
        return ApiResponse::success($item, 'Detalle de producto');
    }

    #[OA\Put(
        path: '/api/gestion-compras/cp-productos/{id}',
        tags: ['CpProductos'],
        summary: 'Actualizar producto',
        security: [['bearerAuth' => []]]
    )]
    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('cp_producto.actualizar');
        try {
            $item = $this->actualizarUseCase->execute($id, $request->all());
            return ApiResponse::success($item, ucfirst('producto') . ' actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/gestion-compras/cp-productos/{id}',
        tags: ['CpProductos'],
        summary: 'Eliminar producto',
        security: [['bearerAuth' => []]]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('cp_producto.eliminar');
        try {
            $this->eliminarUseCase->execute($id);
            return ApiResponse::success(null, ucfirst('producto') . ' eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar: ' . $e->getMessage(), 500);
        }
    }
}