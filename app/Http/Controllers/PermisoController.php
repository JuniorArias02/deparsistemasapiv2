<?php

namespace App\Http\Controllers;

use App\Services\PermisoService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class PermisoController extends Controller
{
    protected $permisoService;

    public function __construct(PermisoService $permisoService)
    {
        $this->permisoService = $permisoService;
    }

    public function index()
    {
        try {
            $permisos = $this->permisoService->getAllPermisos();
            return ApiResponse::success($permisos, 'Lista de permisos obtenida correctamente');
        } catch (Exception $e) {
            return ApiResponse::error('Error al obtener permisos: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:permisos,nombre|max:50',
            'descripcion' => 'nullable|string'
        ]);

        try {
            $permiso = $this->permisoService->createPermiso($request->all());
            return ApiResponse::success($permiso, 'Permiso creado correctamente', 201);
        } catch (Exception $e) {
            return ApiResponse::error('Error al crear permiso: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:permisos,nombre,' . $id,
            'descripcion' => 'nullable|string'
        ]);

        try {
            $permiso = $this->permisoService->updatePermiso($id, $request->all());
            return ApiResponse::success($permiso, 'Permiso actualizado correctamente');
        } catch (Exception $e) {
            return ApiResponse::error('Error al actualizar permiso: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->permisoService->deletePermiso($id);
            return ApiResponse::success(null, 'Permiso eliminado correctamente');
        } catch (Exception $e) {
            return ApiResponse::error('Error al eliminar permiso: ' . $e->getMessage(), 500);
        }
    }

    public function getRoles()
    {
        try {
            $roles = $this->permisoService->getRolesWithPermisos();
            return ApiResponse::success($roles, 'Roles con permisos obtenidos correctamente');
        } catch (Exception $e) {
            return ApiResponse::error('Error al obtener roles: ' . $e->getMessage(), 500);
        }
    }

    public function assignPermisos(Request $request)
    {
        $request->validate([
            'rol_id' => 'required|exists:rol,id',
            'permisos' => 'present|array', // Can be empty array to remove all
            'permisos.*' => 'exists:permisos,id'
        ]);

        try {
            $rol = $this->permisoService->assignPermisosToRol($request->rol_id, $request->permisos);
            return ApiResponse::success($rol, 'Permisos asignados correctamente al rol');
        } catch (Exception $e) {
            return ApiResponse::error('Error al asignar permisos: ' . $e->getMessage(), 500);
        }
    }
}
