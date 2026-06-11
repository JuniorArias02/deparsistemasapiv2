<?php
namespace App\Modules\Configuracion\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Configuracion\Application\UseCases\Permiso\ListarPermisoUseCase;
use App\Modules\Configuracion\Application\UseCases\Permiso\CrearPermisoUseCase;
use App\Modules\Configuracion\Application\UseCases\Permiso\ObtenerPermisoUseCase;
use App\Modules\Configuracion\Application\UseCases\Permiso\ActualizarPermisoUseCase;
use App\Modules\Configuracion\Application\UseCases\Permiso\EliminarPermisoUseCase;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected ListarPermisoUseCase $listarUseCase,
        protected CrearPermisoUseCase $crearUseCase,
        protected ObtenerPermisoUseCase $obtenerUseCase,
        protected ActualizarPermisoUseCase $actualizarUseCase,
        protected EliminarPermisoUseCase $eliminarUseCase
    ) {}

    public function index(Request $request)
    {
        // $this->permissionService->authorize('permiso.listar');
        return ApiResponse::success($this->listarUseCase->execute(), 'Lista de Permisos');
    }

    public function store(Request $request)
    {
        $this->permissionService->authorize('permiso.crear');
        try {
            return ApiResponse::success($this->crearUseCase->execute($request->all()), 'Permiso creado', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        // $this->permissionService->authorize('permiso.listar');
        $item = $this->obtenerUseCase->execute($id);
        if (!$item) return ApiResponse::error('No encontrado', 404);
        return ApiResponse::success($item, 'Detalle de Permiso');
    }

    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('permiso.actualizar');
        try {
            $item = $this->actualizarUseCase->execute($id, $request->all());
            if (!$item) return ApiResponse::error('No encontrado', 404);
            return ApiResponse::success($item, 'Permiso actualizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        $this->permissionService->authorize('permiso.eliminar');
        if ($this->eliminarUseCase->execute($id)) {
            return ApiResponse::success(null, 'Permiso eliminado');
        }
        return ApiResponse::error('No encontrado', 404);
    }

    public function getRoles()
    {
        // $this->permissionService->authorize('permiso.listar');
        return ApiResponse::success(\App\Models\Rol::with('permisos')->get(), 'Lista de roles');
    }

    public function assignPermisos(Request $request)
    {
        $this->permissionService->authorize('permiso.actualizar');
        $request->validate([
            'rol_id' => 'required|exists:rol,id',
            'permisos' => 'required|array',
            'permisos.*' => 'exists:permisos,id',
        ]);
        
        $rol = \App\Models\Rol::find($request->rol_id);
        $rol->permisos()->sync($request->permisos);
        return ApiResponse::success($rol->load('permisos'), 'Permisos actualizados');
    }
}