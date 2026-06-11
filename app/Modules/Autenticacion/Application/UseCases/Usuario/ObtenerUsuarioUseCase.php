<?php

namespace App\Modules\Autenticacion\Application\UseCases\Usuario;

use App\Models\Usuario;

class ObtenerUsuarioUseCase
{
    public function execute($id)
    {
        return Usuario::with(['rol', 'sede'])->findOrFail($id);
    }
}
