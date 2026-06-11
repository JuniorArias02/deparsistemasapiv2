<?php
namespace App\Modules\Configuracion\Application\UseCases\DependenciaSede;
use App\Models\DependenciaSede;

class ListarDependenciaSedeUseCase
{
    public function execute()
    {
        return DependenciaSede::with('sede')->get();
    }
}