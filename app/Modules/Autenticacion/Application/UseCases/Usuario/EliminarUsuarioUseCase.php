<?php

namespace App\Modules\Autenticacion\Application\UseCases\Usuario;

use App\Models\Usuario;

class EliminarUsuarioUseCase
{
    public function execute($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();
        return true;
    }
}
