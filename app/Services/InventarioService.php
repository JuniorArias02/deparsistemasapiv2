<?php

namespace App\Services;

use App\Models\Inventario;
use Carbon\Carbon;

class InventarioService
{
    public function create(array $data)
    {
        // Asignar el usuario autenticado (si hay uno, que debería por el middleware)
        $user = \Illuminate\Support\Facades\Auth::guard('api')->user();
        $data['creado_por'] = $user ? $user->id : null;
        $data['fecha_creacion'] = Carbon::now();
        $data['activo'] = '1';
        $data['codigo2'] = ''; // Requerido por la BD pero no usado por ahora

        // Validar si el código ya existe? (Podría hacerse en request, pero aquí es safe)

        return Inventario::create($data);
    }
}
