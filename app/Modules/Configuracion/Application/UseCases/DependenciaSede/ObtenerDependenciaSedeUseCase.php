<?php
namespace App\Modules\Configuracion\Application\UseCases\DependenciaSede;
use App\Models\DependenciaSede;

class ObtenerDependenciaSedeUseCase
{
    public function execute($id)
    {
        return DependenciaSede::with('sede')->find($id);
    }
}