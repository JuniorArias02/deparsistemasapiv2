<?php
namespace App\Modules\Configuracion\Application\UseCases\Permiso;
use App\Models\Permiso;

class ListarPermisoUseCase
{
    public function execute()
    {
        return Permiso::all();
    }
}