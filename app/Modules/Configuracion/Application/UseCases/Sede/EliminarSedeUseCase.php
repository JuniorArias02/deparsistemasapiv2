<?php
namespace App\Modules\Configuracion\Application\UseCases\Sede;
use App\Models\Sede;

class EliminarSedeUseCase
{
    public function execute($id)
    {
        $item = Sede::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}