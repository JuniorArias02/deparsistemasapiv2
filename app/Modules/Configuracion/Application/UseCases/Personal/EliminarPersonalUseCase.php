<?php
namespace App\Modules\Configuracion\Application\UseCases\Personal;
use App\Models\Personal;

class EliminarPersonalUseCase
{
    public function execute($id)
    {
        $item = Personal::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}