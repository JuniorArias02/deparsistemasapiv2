<?php
namespace App\Modules\Configuracion\Application\UseCases\Rol;
use App\Models\Rol;

class CrearRolUseCase
{
    public function execute(array $data)
    {
        return Rol::create($data);
    }
}