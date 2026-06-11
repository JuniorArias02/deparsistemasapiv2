<?php
namespace App\Modules\Configuracion\Application\UseCases\Rol;
use App\Models\Rol;

class ObtenerRolUseCase
{
    public function execute($id)
    {
        return Rol::find($id);
    }
}