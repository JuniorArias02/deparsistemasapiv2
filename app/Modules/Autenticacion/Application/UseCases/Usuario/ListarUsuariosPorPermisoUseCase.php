<?php

namespace App\Modules\Autenticacion\Application\UseCases\Usuario;

use App\Models\Usuario;

class ListarUsuariosPorPermisoUseCase
{
    public function execute($permiso)
    {
        return Usuario::where('estado', 1)
            ->whereHas('rol.permisos', function ($query) use ($permiso) {
                $query->where('nombre', $permiso);
            })->with('rol')->get();
    }
}
