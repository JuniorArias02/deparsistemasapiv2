<?php

namespace App\Services;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;
use Exception;

class PermisoService
{
    public function getAllPermisos()
    {
        return Permiso::all();
    }

    public function createPermiso(array $data)
    {
        return Permiso::create($data);
    }

    public function updatePermiso($id, array $data)
    {
        $permiso = Permiso::findOrFail($id);
        $permiso->update($data);
        return $permiso;
    }

    public function deletePermiso($id)
    {
        $permiso = Permiso::findOrFail($id);
        $permiso->delete();
        return true;
    }

    public function getRolesWithPermisos()
    {
        return Rol::with('permisos')->get();
    }

    public function assignPermisosToRol($rolId, array $permisoIds)
    {
        $rol = Rol::findOrFail($rolId);
        $rol->permisos()->sync($permisoIds);
        return $rol->load('permisos');
    }
}
