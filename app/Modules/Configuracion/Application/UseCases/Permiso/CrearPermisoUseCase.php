<?php
namespace App\Modules\Configuracion\Application\UseCases\Permiso;
use App\Models\Permiso;

class CrearPermisoUseCase
{
    public function execute(array $data)
    {
        return Permiso::create($data);
    }
}