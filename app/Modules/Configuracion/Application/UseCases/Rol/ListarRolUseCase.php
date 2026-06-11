<?php
namespace App\Modules\Configuracion\Application\UseCases\Rol;
use App\Models\Rol;

class ListarRolUseCase
{
    public function execute()
    {
        return Rol::all();
    }
}