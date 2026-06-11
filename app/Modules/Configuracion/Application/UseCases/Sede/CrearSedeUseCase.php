<?php
namespace App\Modules\Configuracion\Application\UseCases\Sede;
use App\Models\Sede;

class CrearSedeUseCase
{
    public function execute(array $data)
    {
        return Sede::create($data);
    }
}