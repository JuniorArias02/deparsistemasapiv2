<?php

namespace App\Modules\GestionCompras\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\GestionCompras\Application\UseCases\TipoSolicitud\ListarTipoSolicitudUseCase;
use App\Modules\GestionCompras\Application\UseCases\TipoSolicitud\CrearTipoSolicitudUseCase;
use App\Modules\GestionCompras\Application\UseCases\TipoSolicitud\ObtenerTipoSolicitudUseCase;
use App\Modules\GestionCompras\Application\UseCases\TipoSolicitud\ActualizarTipoSolicitudUseCase;
use App\Modules\GestionCompras\Application\UseCases\TipoSolicitud\EliminarTipoSolicitudUseCase;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CpTipoSolicitudController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected ListarTipoSolicitudUseCase $listarUseCase,
        protected CrearTipoSolicitudUseCase $crearUseCase,
        protected ObtenerTipoSolicitudUseCase $obtenerUseCase,
        protected ActualizarTipoSolicitudUseCase $actualizarUseCase,
        protected EliminarTipoSolicitudUseCase $eliminarUseCase
    ) {}

    #[OA\Get(
        path: '/api/gestion-compras/cp-tipos-solicitud',
        tags: ['CpTiposSolicitud'],
        summary: 'Listar tipo de solicitud',
        security: [['bearerAuth' => []]]
    )]
    public function index()
    {
        $items = $this->listarUseCase->execute();
        return ApiResponse::success($items, 'Lista de tipo de solicitud');
    }

    #[OA\Post(
        path: '/api/gestion-compras/cp-tipos-solicitud',
        tags: ['CpTiposSolicitud'],
        summary: 'Crear tipo de solicitud',
        security: [['bearerAuth' => []]]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('cp_tipo_solicitud.crear');
        try {
            $item = $this->crearUseCase->execute($request->all());
            return ApiResponse::success($item, ucfirst('tipo de solicitud') . ' creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/gestion-compras/cp-tipos-solicitud/{id}',
        tags: ['CpTiposSolicitud'],
        summary: 'Obtener tipo de solicitud',
        security: [['bearerAuth' => []]]
    )]
    public function show($id)
    {
        $item = $this->obtenerUseCase->execute($id);
        if (!$item) {
            return ApiResponse::error(ucfirst('tipo de solicitud') . ' no encontrado', 404);
        }
        return ApiResponse::success($item, 'Detalle de tipo de solicitud');
    }

    #[OA\Put(
        path: '/api/gestion-compras/cp-tipos-solicitud/{id}',
        tags: ['CpTiposSolicitud'],
        summary: 'Actualizar tipo de solicitud',
        security: [['bearerAuth' => []]]
    )]
    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('cp_tipo_solicitud.actualizar');
        try {
            $item = $this->actualizarUseCase->execute($id, $request->all());
            return ApiResponse::success($item, ucfirst('tipo de solicitud') . ' actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/gestion-compras/cp-tipos-solicitud/{id}',
        tags: ['CpTiposSolicitud'],
        summary: 'Eliminar tipo de solicitud',
        security: [['bearerAuth' => []]]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('cp_tipo_solicitud.eliminar');
        try {
            $this->eliminarUseCase->execute($id);
            return ApiResponse::success(null, ucfirst('tipo de solicitud') . ' eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar: ' . $e->getMessage(), 500);
        }
    }
}