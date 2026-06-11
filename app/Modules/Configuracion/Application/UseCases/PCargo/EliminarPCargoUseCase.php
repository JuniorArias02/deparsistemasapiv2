<?php
namespace App\Modules\Configuracion\Application\UseCases\PCargo;
use App\Models\PCargo;

class EliminarPCargoUseCase
{
    public function execute($id)
    {
        $item = PCargo::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}