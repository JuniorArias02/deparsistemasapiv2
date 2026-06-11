<?php
namespace App\Modules\Configuracion\Application\UseCases\DependenciaSede;
use App\Models\DependenciaSede;

class CrearDependenciaSedeUseCase
{
    public function execute(array $data)
    {
        return DependenciaSede::create($data);
    }
}