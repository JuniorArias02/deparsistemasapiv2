<?php
namespace App\Modules\Configuracion\Application\UseCases\DatosEmpresa;
use App\Models\DatosEmpresa;

class EliminarDatosEmpresaUseCase
{
    public function execute($id)
    {
        $item = DatosEmpresa::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}