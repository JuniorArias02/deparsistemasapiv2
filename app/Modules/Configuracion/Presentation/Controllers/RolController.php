<?php
namespace App\Modules\Configuracion\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Configuracion\Application\UseCases\Rol\ListarRolUseCase;
use App\Modules\Configuracion\Application\UseCases\Rol\CrearRolUseCase;
use App\Modules\Configuracion\Application\UseCases\Rol\ObtenerRolUseCase;
use App\Modules\Configuracion\Application\UseCases\Rol\ActualizarRolUseCase;
use App\Modules\Configuracion\Application\UseCases\Rol\EliminarRolUseCase;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected ListarRolUseCase $listarUseCase,
        protected CrearRolUseCase $crearUseCase,
        protected ObtenerRolUseCase $obtenerUseCase,
        protected ActualizarRolUseCase $actualizarUseCase,
        protected EliminarRolUseCase $eliminarUseCase
    ) {}

    public function index(Request $request)
    {
        // $this->permissionService->authorize('rol.listar');
        return ApiResponse::success($this->listarUseCase->execute(), 'Lista de Roles');
    }

    public function store(Request $request)
    {
        $this->permissionService->authorize('rol.crear');
        try {
            return ApiResponse::success($this->crearUseCase->execute($request->all()), 'Rol creado', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        // $this->permissionService->authorize('rol.listar');
        $item = $this->obtenerUseCase->execute($id);
        if (!$item) return ApiResponse::error('No encontrado', 404);
        return ApiResponse::success($item, 'Detalle de Rol');
    }

    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('rol.actualizar');
        try {
            $item = $this->actualizarUseCase->execute($id, $request->all());
            if (!$item) return ApiResponse::error('No encontrado', 404);
            return ApiResponse::success($item, 'Rol actualizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        $this->permissionService->authorize('rol.eliminar');
        if ($this->eliminarUseCase->execute($id)) {
            return ApiResponse::success(null, 'Rol eliminado');
        }
        return ApiResponse::error('No encontrado', 404);
    }

    public function assignPermissions(Request $request, $id)
    {
        $this->permissionService->authorize('rol.assign_permission');
        $rol = \App\Models\Rol::find($id);
        if (!$rol) return ApiResponse::error('Rol no encontrado', 404);
        
        $request->validate([
            'permisos' => 'required|array',
            'permisos.*' => 'exists:permisos,id',
        ]);
        
        $rol->permisos()->sync($request->permisos);
        return ApiResponse::success($rol->load('permisos'), 'Permisos asignados');
    }
}