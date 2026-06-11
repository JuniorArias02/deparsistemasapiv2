<?php
namespace App\Modules\Configuracion\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Configuracion\Application\UseCases\Sede\ListarSedeUseCase;
use App\Modules\Configuracion\Application\UseCases\Sede\CrearSedeUseCase;
use App\Modules\Configuracion\Application\UseCases\Sede\ObtenerSedeUseCase;
use App\Modules\Configuracion\Application\UseCases\Sede\ActualizarSedeUseCase;
use App\Modules\Configuracion\Application\UseCases\Sede\EliminarSedeUseCase;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;

class SedeController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected ListarSedeUseCase $listarUseCase,
        protected CrearSedeUseCase $crearUseCase,
        protected ObtenerSedeUseCase $obtenerUseCase,
        protected ActualizarSedeUseCase $actualizarUseCase,
        protected EliminarSedeUseCase $eliminarUseCase
    ) {}

    public function index(Request $request)
    {
        // $this->permissionService->authorize('sede.listar');
        return ApiResponse::success($this->listarUseCase->execute(), 'Lista de Sedes');
    }

    public function store(Request $request)
    {
        $this->permissionService->authorize('sede.crear');
        try {
            return ApiResponse::success($this->crearUseCase->execute($request->all()), 'Sede creado', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        // $this->permissionService->authorize('sede.listar');
        $item = $this->obtenerUseCase->execute($id);
        if (!$item) return ApiResponse::error('No encontrado', 404);
        return ApiResponse::success($item, 'Detalle de Sede');
    }

    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('sede.actualizar');
        try {
            $item = $this->actualizarUseCase->execute($id, $request->all());
            if (!$item) return ApiResponse::error('No encontrado', 404);
            return ApiResponse::success($item, 'Sede actualizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        $this->permissionService->authorize('sede.eliminar');
        if ($this->eliminarUseCase->execute($id)) {
            return ApiResponse::success(null, 'Sede eliminado');
        }
        return ApiResponse::error('No encontrado', 404);
    }
}