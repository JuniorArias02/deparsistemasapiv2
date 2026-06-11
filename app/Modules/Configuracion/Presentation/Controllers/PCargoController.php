<?php
namespace App\Modules\Configuracion\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Configuracion\Application\UseCases\PCargo\ListarPCargoUseCase;
use App\Modules\Configuracion\Application\UseCases\PCargo\CrearPCargoUseCase;
use App\Modules\Configuracion\Application\UseCases\PCargo\ObtenerPCargoUseCase;
use App\Modules\Configuracion\Application\UseCases\PCargo\ActualizarPCargoUseCase;
use App\Modules\Configuracion\Application\UseCases\PCargo\EliminarPCargoUseCase;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;

class PCargoController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected ListarPCargoUseCase $listarUseCase,
        protected CrearPCargoUseCase $crearUseCase,
        protected ObtenerPCargoUseCase $obtenerUseCase,
        protected ActualizarPCargoUseCase $actualizarUseCase,
        protected EliminarPCargoUseCase $eliminarUseCase
    ) {}

    public function index(Request $request)
    {
        // $this->permissionService->authorize('p_cargo.listar');
        return ApiResponse::success($this->listarUseCase->execute(), 'Lista de Cargos');
    }

    public function store(Request $request)
    {
        $this->permissionService->authorize('p_cargo.crear');
        try {
            return ApiResponse::success($this->crearUseCase->execute($request->all()), 'PCargo creado', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        // $this->permissionService->authorize('p_cargo.listar');
        $item = $this->obtenerUseCase->execute($id);
        if (!$item) return ApiResponse::error('No encontrado', 404);
        return ApiResponse::success($item, 'Detalle de PCargo');
    }

    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('p_cargo.actualizar');
        try {
            $item = $this->actualizarUseCase->execute($id, $request->all());
            if (!$item) return ApiResponse::error('No encontrado', 404);
            return ApiResponse::success($item, 'PCargo actualizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        $this->permissionService->authorize('p_cargo.eliminar');
        if ($this->eliminarUseCase->execute($id)) {
            return ApiResponse::success(null, 'PCargo eliminado');
        }
        return ApiResponse::error('No encontrado', 404);
    }
}