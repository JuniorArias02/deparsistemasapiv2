<?php
namespace App\Modules\Configuracion\Application\UseCases\Permiso;
use App\Models\Permiso;

class ObtenerPermisoUseCase
{
    public function execute($id)
    {
        return Permiso::find($id);
    }
}