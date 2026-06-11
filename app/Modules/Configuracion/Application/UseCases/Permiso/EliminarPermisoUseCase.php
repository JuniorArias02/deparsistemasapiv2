<?php
namespace App\Modules\Configuracion\Application\UseCases\Permiso;
use App\Models\Permiso;

class EliminarPermisoUseCase
{
    public function execute($id)
    {
        $item = Permiso::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}