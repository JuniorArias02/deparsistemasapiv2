<?php

namespace App\Modules\GestionCompras\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\GestionCompras\Application\UseCases\Proveedor\ListarProveedorUseCase;
use App\Modules\GestionCompras\Application\UseCases\Proveedor\CrearProveedorUseCase;
use App\Modules\GestionCompras\Application\UseCases\Proveedor\ObtenerProveedorUseCase;
use App\Modules\GestionCompras\Application\UseCases\Proveedor\ActualizarProveedorUseCase;
use App\Modules\GestionCompras\Application\UseCases\Proveedor\EliminarProveedorUseCase;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CpProveedorController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected ListarProveedorUseCase $listarUseCase,
        protected CrearProveedorUseCase $crearUseCase,
        protected ObtenerProveedorUseCase $obtenerUseCase,
        protected ActualizarProveedorUseCase $actualizarUseCase,
        protected EliminarProveedorUseCase $eliminarUseCase
    ) {}

    #[OA\Get(
        path: '/api/gestion-compras/cp-proveedores',
        tags: ['CpProveedores'],
        summary: 'Listar proveedor',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Éxito')]
    )]
    public function index()
    {
        $items = $this->listarUseCase->execute();
        return ApiResponse::success($items, 'Lista de proveedor');
    }

    #[OA\Post(
        path: '/api/gestion-compras/cp-proveedores',
        tags: ['CpProveedores'],
        summary: 'Crear proveedor',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Éxito')]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('cp_proveedor.crear');
        try {
            $item = $this->crearUseCase->execute($request->all());
            return ApiResponse::success($item, ucfirst('proveedor') . ' creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/gestion-compras/cp-proveedores/{id}',
        tags: ['CpProveedores'],
        summary: 'Obtener proveedor',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Éxito')]
    )]
    public function show($id)
    {
        $item = $this->obtenerUseCase->execute($id);
        if (!$item) {
            return ApiResponse::error(ucfirst('proveedor') . ' no encontrado', 404);
        }
        return ApiResponse::success($item, 'Detalle de proveedor');
    }

    #[OA\Put(
        path: '/api/gestion-compras/cp-proveedores/{id}',
        tags: ['CpProveedores'],
        summary: 'Actualizar proveedor',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Éxito')]
    )]
    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('cp_proveedor.actualizar');
        try {
            $item = $this->actualizarUseCase->execute($id, $request->all());
            return ApiResponse::success($item, ucfirst('proveedor') . ' actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/gestion-compras/cp-proveedores/{id}',
        tags: ['CpProveedores'],
        summary: 'Eliminar proveedor',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Éxito')]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('cp_proveedor.eliminar');
        try {
            $this->eliminarUseCase->execute($id);
            return ApiResponse::success(null, ucfirst('proveedor') . ' eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar: ' . $e->getMessage(), 500);
        }
    }
}