<?php
namespace App\Modules\Configuracion\Application\UseCases\DependenciaSede;
use App\Models\DependenciaSede;

class EliminarDependenciaSedeUseCase
{
    public function execute($id)
    {
        $item = DependenciaSede::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}