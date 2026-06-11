<?php
namespace App\Modules\Configuracion\Application\UseCases\Area;
use App\Models\Area;

class EliminarAreaUseCase
{
    public function execute($id)
    {
        $item = Area::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}