<?php
namespace App\Modules\Configuracion\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Configuracion\Application\UseCases\Area\ListarAreaUseCase;
use App\Modules\Configuracion\Application\UseCases\Area\CrearAreaUseCase;
use App\Modules\Configuracion\Application\UseCases\Area\ObtenerAreaUseCase;
use App\Modules\Configuracion\Application\UseCases\Area\ActualizarAreaUseCase;
use App\Modules\Configuracion\Application\UseCases\Area\EliminarAreaUseCase;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected ListarAreaUseCase $listarUseCase,
        protected CrearAreaUseCase $crearUseCase,
        protected ObtenerAreaUseCase $obtenerUseCase,
        protected ActualizarAreaUseCase $actualizarUseCase,
        protected EliminarAreaUseCase $eliminarUseCase
    ) {}

    public function index(Request $request)
    {
        // $this->permissionService->authorize('area.listar');
        return ApiResponse::success($this->listarUseCase->execute($request->all()), 'Lista de Areas');
    }

    public function store(Request $request)
    {
        $this->permissionService->authorize('area.crear');
        try {
            return ApiResponse::success($this->crearUseCase->execute($request->all()), 'Area creado', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        // $this->permissionService->authorize('area.listar');
        $item = $this->obtenerUseCase->execute($id);
        if (!$item) return ApiResponse::error('No encontrado', 404);
        return ApiResponse::success($item, 'Detalle de Area');
    }

    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('area.actualizar');
        try {
            $item = $this->actualizarUseCase->execute($id, $request->all());
            if (!$item) return ApiResponse::error('No encontrado', 404);
            return ApiResponse::success($item, 'Area actualizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        $this->permissionService->authorize('area.eliminar');
        if ($this->eliminarUseCase->execute($id)) {
            return ApiResponse::success(null, 'Area eliminado');
        }
        return ApiResponse::error('No encontrado', 404);
    }
}