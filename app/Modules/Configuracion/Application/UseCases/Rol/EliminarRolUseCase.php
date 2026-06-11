<?php
namespace App\Modules\Configuracion\Application\UseCases\Rol;
use App\Models\Rol;

class EliminarRolUseCase
{
    public function execute($id)
    {
        $item = Rol::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}